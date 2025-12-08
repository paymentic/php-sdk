<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Webhook;

enum WebhookEvent: string
{
    case PAYMENT_TRANSACTION_STATUS_CHANGED = 'PAYMENT.TRANSACTION_STATUS_CHANGED';
    case PAYMENT_REFUND_STATUS_CHANGED = 'PAYMENT.REFUND_STATUS_CHANGED';
    case PAYMENT_TRANSACTION_BLIK_STATUS_CHANGED = 'PAYMENT.TRANSACTION_BLIK_STATUS_CHANGED';

    public function getModule(): string
    {
        return explode('.', $this->value)[0];
    }

    public function isPaymentEvent(): bool
    {
        return $this->getModule() === 'PAYMENT';
    }
}
