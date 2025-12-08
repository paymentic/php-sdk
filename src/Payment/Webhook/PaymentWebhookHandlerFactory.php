<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Webhook;

use Paymentic\Sdk\Payment\Webhook\Payload\RefundStatusChangedPayload;
use Paymentic\Sdk\Payment\Webhook\Payload\TransactionBlikStatusChangedPayload;
use Paymentic\Sdk\Payment\Webhook\Payload\TransactionStatusChangedPayload;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookHandler;

final class PaymentWebhookHandlerFactory
{
    public static function create(string $signatureKey): WebhookHandler
    {
        return (new WebhookHandler($signatureKey))
            ->registerPayloadResolver(
                WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
                static fn (array $data) => TransactionStatusChangedPayload::fromArray($data),
            )
            ->registerPayloadResolver(
                WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED,
                static fn (array $data) => RefundStatusChangedPayload::fromArray($data),
            )
            ->registerPayloadResolver(
                WebhookEvent::PAYMENT_TRANSACTION_BLIK_STATUS_CHANGED,
                static fn (array $data) => TransactionBlikStatusChangedPayload::fromArray($data),
            );
    }
}
