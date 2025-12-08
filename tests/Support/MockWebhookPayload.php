<?php

declare(strict_types=1);

namespace Paymentic\Tests\Support;

use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookPayloadInterface;

final readonly class MockWebhookPayload implements WebhookPayloadInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        public array $data = [],
    ) {
    }

    public static function getEventType(): WebhookEvent
    {
        return WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self($data);
    }
}
