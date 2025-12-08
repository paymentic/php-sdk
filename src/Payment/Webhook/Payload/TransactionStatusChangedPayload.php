<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Webhook\Payload;

use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookPayloadInterface;

final readonly class TransactionStatusChangedPayload implements WebhookPayloadInterface
{
    public function __construct(
        public string $transactionId,
        public string $pointId,
        public TransactionStatus $status,
        public string $amount,
        public string $currency,
        public ?string $commission = null,
        public ?string $externalReferenceId = null,
        public ?string $paymentMethod = null,
        public ?string $paymentChannel = null,
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
        return new self(
            transactionId: $data['transactionId'],
            pointId: $data['pointId'],
            status: TransactionStatus::from($data['status']),
            amount: $data['amount'],
            currency: $data['currency'],
            commission: $data['commission'] ?? null,
            externalReferenceId: $data['externalReferenceId'] ?? null,
            paymentMethod: $data['paymentMethod'] ?? null,
            paymentChannel: $data['paymentChannel'] ?? null,
        );
    }
}
