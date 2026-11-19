<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Symfony\Notifier;

use JsonException;
use Paymentic\Sdk\Messaging\Application\DTO\SendSmsRequest;
use Paymentic\Sdk\Messaging\MessagingClient;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Psr\Http\Client\ClientExceptionInterface;
use Symfony\Component\Notifier\Exception\LogicException;
use Symfony\Component\Notifier\Exception\UnsupportedMessageTypeException;
use Symfony\Component\Notifier\Message\MessageInterface;
use Symfony\Component\Notifier\Message\SentMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport\AbstractTransport;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PaymenticTransport extends AbstractTransport
{
    protected const HOST = 'api.paymentic.com';
    private const SANDBOX_HOST = 'api.sandbox.paymentic.com';

    public function __construct(
        private readonly MessagingClient $messaging,
        private readonly ?string $from = null,
        private readonly bool $sandbox = false,
        ?HttpClientInterface $client = null,
        ?EventDispatcherInterface $dispatcher = null,
    ) {
        parent::__construct($client, $dispatcher);
    }

    public function __toString(): string
    {
        return sprintf('paymentic://%s', $this->getEndpoint())
            . (null !== $this->from ? '?from=' . rawurlencode($this->from) : '');
    }

    public function supports(MessageInterface $message): bool
    {
        return $message instanceof SmsMessage
            && (null === $message->getOptions() || $message->getOptions() instanceof PaymenticOptions);
    }

    protected function getDefaultHost(): string
    {
        return $this->sandbox ? self::SANDBOX_HOST : static::HOST;
    }

    protected function doSend(MessageInterface $message): SentMessage
    {
        if (! $message instanceof SmsMessage) {
            throw new UnsupportedMessageTypeException(__CLASS__, SmsMessage::class, $message);
        }

        $options = $message->getOptions();
        if (null !== $options && ! $options instanceof PaymenticOptions) {
            throw new LogicException(sprintf('The "%s" transport only supports "%s" options.', __CLASS__, PaymenticOptions::class));
        }

        $request = new SendSmsRequest(
            recipient: $message->getPhone(),
            text: $message->getSubject(),
            sender: '' !== $message->getFrom() ? $message->getFrom() : $this->from,
            unicode: $options?->isUnicode(),
            externalReferenceId: $options?->getExternalReferenceId(),
            priority: $options?->getPriority(),
        );

        try {
            $sms = $this->messaging->sms()->send($request, $options?->getIdempotencyKey());
        } catch (PaymenticException|ClientExceptionInterface|JsonException $e) {
            throw new PaymenticTransportException($e->getMessage(), (int) $e->getCode(), $e, $e->getMessage());
        }

        $sent = new SentMessage($message, (string) $this);
        $sent->setMessageId($sms->id);

        return $sent;
    }
}
