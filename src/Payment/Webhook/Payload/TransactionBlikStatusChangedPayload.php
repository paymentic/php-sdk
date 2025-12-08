<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Webhook\Payload;

use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookPayloadInterface;

final readonly class TransactionBlikStatusChangedPayload implements WebhookPayloadInterface
{
    public function __construct(
        public string $transactionId,
        public string $actionId,
        public string $externalStatus,
        public string $externalId,
    ) {
    }

    public static function getEventType(): WebhookEvent
    {
        return WebhookEvent::PAYMENT_TRANSACTION_BLIK_STATUS_CHANGED;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: $data['transactionId'],
            actionId: $data['actionId'],
            externalStatus: $data['externalStatus'],
            externalId: $data['externalId'],
        );
    }
}
