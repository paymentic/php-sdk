<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Shared\Webhook;

use Paymentic\Sdk\Shared\Webhook\Webhook;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookHeaders;
use Paymentic\Tests\Support\MockWebhookPayload;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WebhookTest extends TestCase
{
    #[Test]
    public function returnsEvent(): void
    {
        $webhook = $this->createWebhook(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED);

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, $webhook->getEvent());
    }

    #[Test]
    public function returnsNotificationId(): void
    {
        $webhook = $this->createWebhook(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, 'notification-123');

        $this->assertSame('notification-123', $webhook->getNotificationId());
    }

    #[Test]
    public function returnsModuleForPaymentEvent(): void
    {
        $webhook = $this->createWebhook(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED);

        $this->assertSame('PAYMENT', $webhook->getModule());
    }

    #[Test]
    public function returnsModuleForRefundEvent(): void
    {
        $webhook = $this->createWebhook(WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED);

        $this->assertSame('PAYMENT', $webhook->getModule());
    }

    #[Test]
    public function exposesRawBody(): void
    {
        $rawBody = '{"test": "data"}';
        $webhook = $this->createWebhook(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, 'id', $rawBody);

        $this->assertSame($rawBody, $webhook->rawBody);
    }

    private function createWebhook(
        WebhookEvent $event,
        string $notificationId = 'test-notification-id',
        string $rawBody = '{}',
    ): Webhook {
        $headers = new WebhookHeaders(
            event: $event,
            notificationId: $notificationId,
            time: '2024-09-20T09:48:03+02:00',
            signature: 'test-signature',
            userAgent: 'Paymentic/1.2',
        );

        $payload = new MockWebhookPayload();

        return new Webhook($headers, $payload, $rawBody);
    }
}
