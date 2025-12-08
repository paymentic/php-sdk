<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Shared\Webhook;

use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WebhookEventTest extends TestCase
{
    #[Test]
    public function returnsModuleForPaymentEvents(): void
    {
        $this->assertSame('PAYMENT', WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED->getModule());
        $this->assertSame('PAYMENT', WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED->getModule());
        $this->assertSame('PAYMENT', WebhookEvent::PAYMENT_TRANSACTION_BLIK_STATUS_CHANGED->getModule());
    }

    #[Test]
    public function identifiesPaymentEvents(): void
    {
        $this->assertTrue(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED->isPaymentEvent());
        $this->assertTrue(WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED->isPaymentEvent());
        $this->assertTrue(WebhookEvent::PAYMENT_TRANSACTION_BLIK_STATUS_CHANGED->isPaymentEvent());
    }

    #[Test]
    public function hasCorrectValues(): void
    {
        $this->assertSame('PAYMENT.TRANSACTION_STATUS_CHANGED', WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED->value);
        $this->assertSame('PAYMENT.REFUND_STATUS_CHANGED', WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED->value);
        $this->assertSame('PAYMENT.TRANSACTION_BLIK_STATUS_CHANGED', WebhookEvent::PAYMENT_TRANSACTION_BLIK_STATUS_CHANGED->value);
    }

    #[Test]
    public function canBeCreatedFromString(): void
    {
        $event = WebhookEvent::from('PAYMENT.TRANSACTION_STATUS_CHANGED');

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, $event);
    }
}
