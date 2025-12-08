<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Webhook;

use ValueError;

final readonly class WebhookHeaders
{
    public WebhookEvent $event;

    /**
     * @param WebhookEvent|string $event
     * @throws ValueError
     */
    public function __construct(
        WebhookEvent|string $event,
        public string $notificationId,
        public string $time,
        public string $signature,
        public string $userAgent,
    ) {
        $this->event = $event instanceof WebhookEvent
            ? $event
            : WebhookEvent::from($event);
    }

    public function getVersion(): string
    {
        if (preg_match('/Paymentic\/(\d+\.\d+)/', $this->userAgent, $matches)) {
            return $matches[1];
        }

        return '1.2';
    }

    /**
     * @param array<string, string> $headers
     */
    public static function fromArray(array $headers): self
    {
        $normalizedHeaders = [];
        foreach ($headers as $key => $value) {
            $normalizedHeaders[strtolower($key)] = $value;
        }

        return new self(
            event: $normalizedHeaders['x-paymentic-event'] ?? '',
            notificationId: $normalizedHeaders['x-paymentic-notification-id'] ?? '',
            time: $normalizedHeaders['x-paymentic-time'] ?? '',
            signature: $normalizedHeaders['x-paymentic-signature'] ?? '',
            userAgent: $normalizedHeaders['user-agent'] ?? '',
        );
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'x-paymentic-event' => $this->event->value,
            'x-paymentic-notification-id' => $this->notificationId,
            'x-paymentic-time' => $this->time,
            'x-paymentic-signature' => $this->signature,
            'user-agent' => $this->userAgent,
        ];
    }
}
