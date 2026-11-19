<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Mailer;

use JsonException;
use Paymentic\Sdk\Messaging\Application\Contract\EmailServiceContract;
use Paymentic\Sdk\Messaging\Application\DTO\SendEmailRequest;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailAttachmentDisposition;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\ValueObject\EmailAttachment;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\InvalidArgumentException;
use Symfony\Component\Mailer\Exception\LogicException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\MessageConverter;
use Symfony\Component\Mime\Part\DataPart;

final class PaymenticMailerTransport extends AbstractTransport
{
    private const STANDARD_HEADERS = [
        'from',
        'to',
        'cc',
        'bcc',
        'reply-to',
        'sender',
        'subject',
        'content-type',
        'mime-version',
        'date',
        'message-id',
        'return-path',
    ];

    public function __construct(
        private readonly EmailServiceContract $emails,
        ?EventDispatcherInterface $dispatcher = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($dispatcher, $logger);
    }

    public function __toString(): string
    {
        return 'paymentic+api://default';
    }

    protected function doSend(SentMessage $message): void
    {
        $original = $message->getOriginalMessage();
        if (! $original instanceof Message) {
            throw new LogicException(sprintf('The "%s" transport can only send "%s" messages.', self::class, Message::class));
        }

        $email = MessageConverter::toEmail($original);
        $envelope = $message->getEnvelope();

        $from = $email->getFrom()[0] ?? $envelope->getSender();

        [$to, $cc, $bcc] = $this->recipients($email, $envelope->getRecipients());

        if ([] === $to) {
            throw new InvalidArgumentException('The Paymentic Email API requires at least one "to" recipient, but the message has none (Bcc-only messages are not supported).');
        }

        $idempotencyKey = null;
        $priority = null;
        $externalReferenceId = null;
        $metadata = [];
        $headers = [];

        /** @var array<string, array{name: string, values: list<string>}> $custom */
        $custom = [];

        foreach ($email->getHeaders()->all() as $name => $header) {
            $lower = strtolower((string) $name);

            if (in_array($lower, self::STANDARD_HEADERS, true) || $header instanceof TagHeader) {
                continue;
            }

            $value = $header instanceof UnstructuredHeader ? $header->getValue() : $header->getBodyAsString();

            if ($header instanceof MetadataHeader) {
                $metadata[$header->getKey()] = $value;
            } elseif ($lower === strtolower(PaymenticMailerHeaders::IDEMPOTENCY_KEY)) {
                $idempotencyKey = '' === $value ? null : $value;
            } elseif ($lower === strtolower(PaymenticMailerHeaders::PRIORITY)) {
                $priority = MessagePriority::tryFrom(strtoupper(trim($value)))
                    ?? throw new InvalidArgumentException(sprintf(
                        'Invalid "%s" header value "%s", expected LOW, MEDIUM or HIGH.',
                        PaymenticMailerHeaders::PRIORITY,
                        $value,
                    ));
            } elseif ($lower === strtolower(PaymenticMailerHeaders::EXTERNAL_REFERENCE_ID)) {
                $externalReferenceId = '' === $value ? null : $value;
            } elseif (str_starts_with($lower, strtolower(PaymenticMailerHeaders::METADATA_PREFIX))) {
                $key = substr($header->getName(), strlen(PaymenticMailerHeaders::METADATA_PREFIX));
                if ('' !== $key) {
                    $metadata[$key] = $value;
                }
            } else {
                $custom[$lower] ??= ['name' => $header->getName(), 'values' => []];
                $custom[$lower]['values'][] = $value;
            }
        }

        foreach ($custom as $entry) {
            $headers[$entry['name']] = implode(', ', $entry['values']);
        }

        $htmlBody = $this->body($email->getHtmlBody());
        $attachments = [];
        foreach ($email->getAttachments() as $part) {
            $attachments[] = $this->attachment($part, $htmlBody);
        }

        $request = new SendEmailRequest(
            from: $from->getAddress(),
            to: $to,
            subject: (string) $email->getSubject(),
            fromName: '' === $from->getName() ? null : $from->getName(),
            html: $htmlBody,
            text: $this->body($email->getTextBody()),
            cc: [] === $cc ? null : $cc,
            bcc: [] === $bcc ? null : $bcc,
            replyTo: [] === $email->getReplyTo() ? null : $this->addresses($email->getReplyTo()),
            headers: [] === $headers ? null : $headers,
            attachments: [] === $attachments ? null : $attachments,
            metadata: [] === $metadata ? null : $metadata,
            externalReferenceId: $externalReferenceId,
            priority: $priority,
        );

        try {
            $emailMessage = $this->emails->send($request, $idempotencyKey);
        } catch (PaymenticException|ClientExceptionInterface|JsonException $e) {
            throw new TransportException(
                sprintf('Request to the Paymentic API failed. Reason: %s', $e->getMessage()),
                $e->getCode(),
                $e,
            );
        }

        $this->getLogger()->debug(sprintf('Paymentic accepted e-mail "%s".', $emailMessage->id));

        $message->setMessageId($emailMessage->id);
    }

    /**
     * @param Address[] $addresses
     *
     * @return array<int, string>
     */
    private function addresses(array $addresses): array
    {
        return array_values(array_map(static fn (Address $address): string => $address->getAddress(), $addresses));
    }

    private function body(mixed $body): ?string
    {
        if (is_resource($body)) {
            if (stream_get_meta_data($body)['seekable']) {
                rewind($body);
            }

            $body = stream_get_contents($body);
        }

        return is_string($body) ? $body : null;
    }

    private function attachment(DataPart $part, ?string $html): EmailAttachment
    {
        $filename = $part->getFilename() ?? 'attachment';
        $inline = 'inline' === $part->getDisposition()
            || $part->hasContentId()
            || (null !== $html && str_contains($html, 'cid:' . $filename));

        return new EmailAttachment(
            filename: $filename,
            content: base64_encode($part->getBody()),
            contentType: $part->getContentType(),
            disposition: $inline ? EmailAttachmentDisposition::INLINE : EmailAttachmentDisposition::ATTACHMENT,
            contentId: $inline ? ($part->hasContentId() ? $part->getContentId() : $filename) : null,
        );
    }

    /**
     * @param Address[] $envelopeRecipients
     *
     * @return array{0: list<string>, 1: list<string>, 2: list<string>}
     */
    private function recipients(Email $email, array $envelopeRecipients): array
    {
        $allowed = [];
        foreach ($envelopeRecipients as $recipient) {
            $allowed[strtolower($recipient->getAddress())] ??= $recipient->getAddress();
        }

        $seen = [];
        $pick = static function (array $addresses) use ($allowed, &$seen): array {
            $result = [];
            foreach ($addresses as $address) {
                $key = strtolower($address->getAddress());
                if (isset($allowed[$key]) && ! isset($seen[$key])) {
                    $seen[$key] = true;
                    $result[] = $address->getAddress();
                }
            }

            return $result;
        };

        $to = $pick($email->getTo());
        $cc = $pick($email->getCc());

        $headerCcBcc = [];
        foreach ([...$email->getCc(), ...$email->getBcc()] as $address) {
            $headerCcBcc[strtolower($address->getAddress())] = true;
        }

        $bcc = [];
        $override = [];
        foreach ($allowed as $key => $address) {
            if (isset($seen[$key])) {
                continue;
            }

            if (isset($headerCcBcc[$key])) {
                $bcc[] = $address;
            } else {
                $override[] = $address;
            }
        }

        if ([] === $to) {
            $to = $override;
        } else {
            $bcc = [...$bcc, ...$override];
        }

        return [$to, $cc, $bcc];
    }
}
