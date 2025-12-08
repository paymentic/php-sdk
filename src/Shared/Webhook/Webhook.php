<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Webhook;

final readonly class Webhook
{
    public function __construct(
        public WebhookHeaders $headers,
        public WebhookPayloadInterface $payload,
        public string $rawBody,
    ) {
    }

    public function getEvent(): WebhookEvent
    {
        return $this->headers->event;
    }

    public function getNotificationId(): string
    {
        return $this->headers->notificationId;
    }

    public function getModule(): string
    {
        return $this->headers->event->getModule();
    }
}
