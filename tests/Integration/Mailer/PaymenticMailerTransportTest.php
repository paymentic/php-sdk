<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Mailer;

use JsonException;
use Paymentic\Sdk\Integration\Mailer\PaymenticMailerHeaders;
use Paymentic\Sdk\Integration\Mailer\PaymenticMailerTransport;
use Paymentic\Sdk\Messaging\Application\Contract\EmailServiceContract;
use Paymentic\Sdk\Messaging\Application\DTO\SendEmailRequest;
use Paymentic\Sdk\Messaging\Domain\Entity\EmailMessage;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailAttachmentDisposition;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailStatus;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\Enum\MessageSource;
use Paymentic\Sdk\Shared\Exception\PaymentRequiredException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use RuntimeException;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\InvalidArgumentException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\Header\TagHeader;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Part\DataPart;
use Throwable;

final class PaymenticMailerTransportTest extends TestCase
{
    #[Test]
    public function sendsBasicEmail(): void
    {
        [$transport, $fake] = $this->createTransport();

        $sent = $transport->send(
            (new Email())
                ->from(new Address('shop@example.org', 'My Shop'))
                ->to('jan@example.org', 'anna@example.org')
                ->subject('Hello')
                ->text('Plain')
                ->html('<p>Html</p>'),
        );

        $request = $this->request($fake);
        $this->assertSame('shop@example.org', $request->from);
        $this->assertSame('My Shop', $request->fromName);
        $this->assertSame(['jan@example.org', 'anna@example.org'], $request->to);
        $this->assertSame('Hello', $request->subject);
        $this->assertSame('Plain', $request->text);
        $this->assertSame('<p>Html</p>', $request->html);
        $this->assertNull($request->cc);
        $this->assertNull($request->bcc);
        $this->assertNull($request->replyTo);
        $this->assertNull($request->attachments);
        $this->assertNull($request->metadata);
        $this->assertNull($request->priority);
        $this->assertNull($fake->idempotencyKey);
        $this->assertNotNull($sent);
        $this->assertSame('01JZX8K3M9QABCDEF0123456789', $sent->getMessageId());
    }

    #[Test]
    public function fromWithoutNameHasNullFromName(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send($this->email()->from('shop@example.org'));

        $this->assertNull($this->request($fake)->fromName);
    }

    #[Test]
    public function usesEnvelopeSenderWhenThereIsNoFromAddress(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send(
            (new Email())->sender('bounce@example.org')->to('jan@example.org')->subject('Hi')->text('x'),
            new Envelope(new Address('bounce@example.org'), [new Address('jan@example.org')]),
        );

        $request = $this->request($fake);
        $this->assertSame('bounce@example.org', $request->from);
        $this->assertNull($request->fromName);
    }

    #[Test]
    public function mapsCcBccAndReplyTo(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send(
            $this->email()
                ->cc('cc@example.org')
                ->bcc('hidden@example.org')
                ->replyTo('reply@example.org'),
        );

        $request = $this->request($fake);
        $this->assertSame(['jan@example.org'], $request->to);
        $this->assertSame(['cc@example.org'], $request->cc);
        $this->assertSame(['hidden@example.org'], $request->bcc);
        $this->assertSame(['reply@example.org'], $request->replyTo);
    }

    #[Test]
    public function bccComesFromEnvelopeEvenWithoutHeader(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send(
            $this->email()->cc('cc@example.org'),
            new Envelope(
                new Address('shop@example.org'),
                [new Address('jan@example.org'), new Address('CC@example.org'), new Address('secret@example.org')],
            ),
        );

        $request = $this->request($fake);
        $this->assertSame(['cc@example.org'], $request->cc);
        $this->assertSame(['secret@example.org'], $request->bcc);
    }

    #[Test]
    public function passesCustomHeadersThroughAndSkipsStandardOnes(): void
    {
        [$transport, $fake] = $this->createTransport();

        $email = $this->email()->replyTo('reply@example.org');
        $email->getHeaders()->addTextHeader('X-Campaign', 'spring');
        $email->getHeaders()->addTextHeader('List-Unsubscribe', '<mailto:unsub@example.org>');
        $email->getHeaders()->addDateHeader('Date', new \DateTimeImmutable());
        $email->getHeaders()->addIdHeader('Message-ID', 'abc@example.org');

        $transport->send($email);

        $this->assertSame([
            'X-Campaign' => 'spring',
            'List-Unsubscribe' => '<mailto:unsub@example.org>',
        ], $this->request($fake)->headers);
    }

    #[Test]
    public function mapsPaymenticHeadersAndStripsThem(): void
    {
        [$transport, $fake] = $this->createTransport();

        $email = $this->email();
        $headers = $email->getHeaders();
        $headers->addTextHeader(PaymenticMailerHeaders::IDEMPOTENCY_KEY, 'key-123');
        $headers->addTextHeader(PaymenticMailerHeaders::PRIORITY, 'high');
        $headers->addTextHeader(PaymenticMailerHeaders::EXTERNAL_REFERENCE_ID, 'order-77');
        $headers->addTextHeader(PaymenticMailerHeaders::METADATA_PREFIX . 'OrderId', '77');
        $headers->addTextHeader(PaymenticMailerHeaders::METADATA_PREFIX . 'Tenant', 'acme');
        $headers->addTextHeader('X-Other', 'kept');

        $transport->send($email);

        $request = $this->request($fake);
        $this->assertSame('key-123', $fake->idempotencyKey);
        $this->assertSame(MessagePriority::HIGH, $request->priority);
        $this->assertSame('order-77', $request->externalReferenceId);
        $this->assertSame(['OrderId' => '77', 'Tenant' => 'acme'], $request->metadata);
        $this->assertSame(['X-Other' => 'kept'], $request->headers);
    }

    #[Test]
    public function invalidPriorityIsNotATransportException(): void
    {
        [$transport] = $this->createTransport();

        $email = $this->email();
        $email->getHeaders()->addTextHeader(PaymenticMailerHeaders::PRIORITY, 'urgent');

        try {
            $transport->send($email);
            $this->fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $e) {
            $this->assertNotInstanceOf(TransportExceptionInterface::class, $e);
        }
    }

    #[Test]
    public function emptyToIsNotATransportException(): void
    {
        [$transport, $fake] = $this->createTransport();

        try {
            $transport->send((new Email())->from('shop@example.org')->bcc('hidden@example.org')->subject('x')->text('y'));
            $this->fail('Expected InvalidArgumentException.');
        } catch (InvalidArgumentException $e) {
            $this->assertNotInstanceOf(TransportExceptionInterface::class, $e);
            $this->assertNull($fake->request);
        }
    }

    #[Test]
    public function envelopeIsAuthoritativeForRecipients(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send(
            (new Email())->from('shop@example.org')->to('real@example.org')->cc('realcc@example.org')->subject('x')->text('y'),
            new Envelope(new Address('shop@example.org'), [new Address('dev@example.org')]),
        );

        $request = $this->request($fake);
        $this->assertSame(['dev@example.org'], $request->to);
        $this->assertNull($request->cc);
        $this->assertNull($request->bcc);
    }

    #[Test]
    public function envelopeRestrictsHeaderRecipients(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send(
            $this->email()->to('jan@example.org', 'other@example.org')->cc('cc@example.org', 'dropped@example.org'),
            new Envelope(new Address('shop@example.org'), [new Address('JAN@example.org'), new Address('cc@example.org')]),
        );

        $request = $this->request($fake);
        $this->assertSame(['jan@example.org'], $request->to);
        $this->assertSame(['cc@example.org'], $request->cc);
        $this->assertNull($request->bcc);
    }

    #[Test]
    public function deduplicatesRecipientsCaseInsensitivelyWithToCcBccPriority(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send(
            (new Email())->from('shop@example.org')
                ->to('a@example.org', 'A@example.org')
                ->cc('a@example.org', 'b@example.org', 'B@example.org')
                ->bcc('b@example.org', 'c@example.org', 'C@example.org')
                ->subject('x')->text('y'),
        );

        $request = $this->request($fake);
        $this->assertSame(['a@example.org'], $request->to);
        $this->assertSame(['b@example.org'], $request->cc);
        $this->assertSame(['c@example.org'], $request->bcc);
    }

    #[Test]
    public function forwardsNonAsciiHeadersUnencoded(): void
    {
        [$transport, $fake] = $this->createTransport();

        $email = $this->email();
        $email->getHeaders()->addTextHeader(PaymenticMailerHeaders::METADATA_PREFIX . 'Customer', 'Józef Wąs');
        $email->getHeaders()->addTextHeader(PaymenticMailerHeaders::EXTERNAL_REFERENCE_ID, 'zamówienie-1');
        $email->getHeaders()->addTextHeader('X-Campaign', 'Wiosenna wyprzedaż');

        $transport->send($email);

        $request = $this->request($fake);
        $this->assertSame(['Customer' => 'Józef Wąs'], $request->metadata);
        $this->assertSame('zamówienie-1', $request->externalReferenceId);
        $this->assertSame(['X-Campaign' => 'Wiosenna wyprzedaż'], $request->headers);
    }

    #[Test]
    public function mapsMetadataHeaderDropsTagHeaderAndReturnPath(): void
    {
        [$transport, $fake] = $this->createTransport();

        $email = $this->email()->returnPath('bounce@example.org');
        $email->getHeaders()->add(new TagHeader('welcome'));
        $email->getHeaders()->add(new TagHeader('second'));
        $email->getHeaders()->add(new MetadataHeader('order', '42'));

        $transport->send($email);

        $request = $this->request($fake);
        $this->assertSame(['order' => '42'], $request->metadata);
        $this->assertNull($request->headers);
    }

    #[Test]
    public function joinsDuplicateCustomHeaders(): void
    {
        [$transport, $fake] = $this->createTransport();

        $email = $this->email();
        $email->getHeaders()->addTextHeader('X-Custom', '1');
        $email->getHeaders()->addTextHeader('x-custom', '2');

        $transport->send($email);

        $this->assertSame(['X-Custom' => '1, 2'], $this->request($fake)->headers);
    }

    #[Test]
    public function embedUsesCidFromHtml(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send($this->email(false)->html('<img src="cid:logo">')->embed('PNGDATA', 'logo', 'image/png'));

        $this->assertInline($fake, 'logo');
    }

    #[Test]
    public function inlineDataPartUsesCidFromHtml(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send(
            $this->email(false)->html('<img src="cid:logo">')
                ->addPart((new DataPart('PNGDATA', 'logo', 'image/png'))->asInline()),
        );

        $this->assertInline($fake, 'logo');
    }

    #[Test]
    public function attachmentReferencedByCidIsInline(): void
    {
        [$transport, $fake] = $this->createTransport();

        $transport->send($this->email(false)->html('<img src="cid:logo.png">')->attach('PNGDATA', 'logo.png', 'image/png'));

        $this->assertInline($fake, 'logo.png');
    }

    #[Test]
    public function readsResourceBodies(): void
    {
        [$transport, $fake] = $this->createTransport();

        $text = fopen('php://memory', 'r+');
        $this->assertIsResource($text);
        fwrite($text, 'From stream');
        rewind($text);

        $transport->send($this->email(false)->text($text));

        $this->assertSame('From stream', $this->request($fake)->text);
    }

    #[Test]
    public function mapsAttachmentsAndInlineParts(): void
    {
        [$transport, $fake] = $this->createTransport();

        $email = $this->email()
            ->attach('PDFDATA', 'invoice.pdf', 'application/pdf')
            ->addPart((new DataPart('PNGDATA', 'logo.png', 'image/png'))->asInline()->setContentId('logo@example.org'));

        $transport->send($email);

        $attachments = $this->request($fake)->attachments;
        $this->assertNotNull($attachments);
        $this->assertCount(2, $attachments);

        $this->assertSame('invoice.pdf', $attachments[0]->filename);
        $this->assertSame(base64_encode('PDFDATA'), $attachments[0]->content);
        $this->assertSame('application/pdf', $attachments[0]->contentType);
        $this->assertSame(EmailAttachmentDisposition::ATTACHMENT, $attachments[0]->disposition);
        $this->assertNull($attachments[0]->contentId);

        $this->assertSame('logo.png', $attachments[1]->filename);
        $this->assertSame(base64_encode('PNGDATA'), $attachments[1]->content);
        $this->assertSame('image/png', $attachments[1]->contentType);
        $this->assertSame(EmailAttachmentDisposition::INLINE, $attachments[1]->disposition);
        $this->assertSame('logo@example.org', $attachments[1]->contentId);
    }

    #[Test]
    public function embeddedPartsAreInline(): void
    {
        [$transport, $fake] = $this->createTransport();

        $email = $this->email()->embed('IMG', 'pic.jpg', 'image/jpeg');

        $transport->send($email);

        $attachments = $this->request($fake)->attachments;
        $this->assertNotNull($attachments);
        $this->assertSame(EmailAttachmentDisposition::INLINE, $attachments[0]->disposition);
        $this->assertNotNull($attachments[0]->contentId);
    }

    #[Test]
    public function setsMessageIdOnSentMessage(): void
    {
        [$transport] = $this->createTransport();

        $sent = $transport->send($this->email());

        $this->assertNotNull($sent);
        $this->assertSame('01JZX8K3M9QABCDEF0123456789', $sent->getMessageId());
    }

    #[Test]
    public function toStringDescribesTransport(): void
    {
        [$transport] = $this->createTransport();

        $this->assertSame('paymentic+api://default', (string) $transport);
    }

    #[Test]
    public function wrapsPaymenticException(): void
    {
        $previous = new PaymentRequiredException('Insufficient funds', [], 402);

        $this->assertWrapped($previous);
    }

    #[Test]
    public function wrapsClientException(): void
    {
        $previous = new class ('network down') extends RuntimeException implements ClientExceptionInterface {
        };

        $this->assertWrapped($previous);
    }

    #[Test]
    public function wrapsJsonException(): void
    {
        $this->assertWrapped(new JsonException('Syntax error'));
    }

    private function assertInline(FakeEmailService $fake, string $cid): void
    {
        $request = $this->request($fake);
        $this->assertNotNull($request->attachments);
        $this->assertStringContainsString('cid:' . $cid . '"', (string) $request->html);
        $this->assertSame(EmailAttachmentDisposition::INLINE, $request->attachments[0]->disposition);
        $this->assertSame($cid, $request->attachments[0]->contentId);
    }

    private function assertWrapped(Throwable $previous): void
    {
        [$transport] = $this->createTransport($previous);

        try {
            $transport->send($this->email());
            $this->fail('Expected TransportException.');
        } catch (TransportException $e) {
            $this->assertSame($previous, $e->getPrevious());
            $this->assertStringContainsString($previous->getMessage(), $e->getMessage());
        }
    }

    private function email(bool $withBody = true): Email
    {
        $email = (new Email())
            ->from('shop@example.org')
            ->to('jan@example.org')
            ->subject('Subject');

        return $withBody ? $email->text('Body') : $email;
    }

    private function request(FakeEmailService $fake): SendEmailRequest
    {
        $this->assertNotNull($fake->request);

        return $fake->request;
    }

    /**
     * @return array{0: PaymenticMailerTransport, 1: FakeEmailService}
     */
    private function createTransport(?Throwable $throw = null): array
    {
        $fake = new FakeEmailService($throw);

        return [new PaymenticMailerTransport($fake), $fake];
    }
}

final class FakeEmailService implements EmailServiceContract
{
    public ?SendEmailRequest $request = null;
    public ?string $idempotencyKey = null;

    public function __construct(private readonly ?Throwable $throw = null)
    {
    }

    public function send(SendEmailRequest $request, ?string $idempotencyKey = null): EmailMessage
    {
        if (null !== $this->throw) {
            throw $this->throw;
        }

        $this->request = $request;
        $this->idempotencyKey = $idempotencyKey;

        return new EmailMessage(
            id: '01JZX8K3M9QABCDEF0123456789',
            status: EmailStatus::QUEUED,
            from: $request->from,
            subject: $request->subject,
            recipients: [],
            recipientsCount: 1,
            freeRecipientsCount: 1,
            billableRecipientsCount: 0,
            attachmentsCount: 0,
            attachmentsBillableMb: 0,
            recipientsCost: '0.000',
            attachmentsCost: '0.000',
            netCost: '0.000',
            vatRate: '23.00',
            totalCost: '0.000',
            source: MessageSource::API,
            priority: MessagePriority::MEDIUM,
        );
    }

    public function get(string $messageId): EmailMessage
    {
        throw new RuntimeException('Not used.');
    }
}
