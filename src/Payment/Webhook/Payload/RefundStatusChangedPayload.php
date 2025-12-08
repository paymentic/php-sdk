<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Webhook\Payload;

use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookPayloadInterface;

final readonly class RefundStatusChangedPayload implements WebhookPayloadInterface
{
    public function __construct(
        public string $refundId,
        public string $transactionId,
        public string $pointId,
        public RefundStatus $status,
        public string $amount,
        public ?string $externalReferenceId = null,
    ) {
    }

    public static function getEventType(): WebhookEvent
    {
        return WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED;
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            refundId: $data['refundId'],
            transactionId: $data['transactionId'],
            pointId: $data['pointId'],
            status: RefundStatus::from($data['status']),
            amount: $data['amount'],
            externalReferenceId: $data['externalReferenceId'] ?? null,
        );
    }
}
