<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Webhook;

use JsonException;
use Paymentic\Sdk\Shared\Webhook\Exception\InvalidWebhookSignatureException;
use Paymentic\Sdk\Shared\Webhook\Exception\UnsupportedWebhookEventException;

final class WebhookHandler
{
    /** @var array<string, callable(array<string, mixed>): WebhookPayloadInterface> */
    private array $payloadResolvers = [];

    public function __construct(
        private readonly string $signatureKey,
    ) {
    }

    /**
     * @param callable(array<string, mixed>): WebhookPayloadInterface $resolver
     */
    public function registerPayloadResolver(WebhookEvent $event, callable $resolver): self
    {
        $this->payloadResolvers[$event->value] = $resolver;

        return $this;
    }

    /**
     * @param array<string, string>|WebhookHeaders $headers
     * @throws InvalidWebhookSignatureException
     * @throws UnsupportedWebhookEventException
     * @throws JsonException
     */
    public function handle(array|WebhookHeaders $headers, string $rawBody): Webhook
    {
        $webhookHeaders = $headers instanceof WebhookHeaders
            ? $headers
            : WebhookHeaders::fromArray($headers);

        $this->verifySignature($webhookHeaders, $rawBody);

        $payload = $this->parsePayload($webhookHeaders->event, $rawBody);

        return new Webhook(
            headers: $webhookHeaders,
            payload: $payload,
            rawBody: $rawBody,
        );
    }

    /**
     * @throws InvalidWebhookSignatureException
     */
    private function verifySignature(WebhookHeaders $headers, string $rawBody): void
    {
        $signatureData = sprintf(
            '%s|%s|%s|%s|%s',
            $headers->event->value,
            $headers->getVersion(),
            $rawBody,
            $headers->notificationId,
            $headers->time,
        );

        $expectedSignature = base64_encode(
            string: hash_hmac('sha512', $signatureData, $this->signatureKey, true),
        );

        if (false === hash_equals($expectedSignature, $headers->signature)) {
            throw new InvalidWebhookSignatureException();
        }
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws JsonException
     */
    private function parsePayload(WebhookEvent $event, string $rawBody): WebhookPayloadInterface
    {
        if (! isset($this->payloadResolvers[$event->value])) {
            throw new UnsupportedWebhookEventException($event->value);
        }

        /** @var array<string, mixed> $data */
        $data = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);

        return ($this->payloadResolvers[$event->value])($data);
    }
}
