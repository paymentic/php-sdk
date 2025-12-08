<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Payment\Webhook;

use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\Payment\Webhook\Payload\RefundStatusChangedPayload;
use Paymentic\Sdk\Payment\Webhook\Payload\TransactionBlikStatusChangedPayload;
use Paymentic\Sdk\Payment\Webhook\Payload\TransactionStatusChangedPayload;
use Paymentic\Sdk\Payment\Webhook\PaymentWebhookHandlerFactory;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymentWebhookHandlerFactoryTest extends TestCase
{
    private const SIGNATURE_KEY = 'test-payment-webhook-secret';

    #[Test]
    public function createsWebhookHandler(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $this->assertInstanceOf(WebhookHandler::class, $handler);
    }

    #[Test]
    public function handlesTransactionStatusChangedEvent(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{"transactionId":"TXN-123","pointId":"30dd1836","status":"CREATED","amount":"100.00","currency":"PLN"}';
        $headers = $this->createHeaders('PAYMENT.TRANSACTION_STATUS_CHANGED', $rawBody);

        $webhook = $handler->handle($headers, $rawBody);

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, $webhook->getEvent());
        $this->assertInstanceOf(TransactionStatusChangedPayload::class, $webhook->payload);
        $this->assertSame('TXN-123', $webhook->payload->transactionId);
        $this->assertSame('30dd1836', $webhook->payload->pointId);
        $this->assertSame(TransactionStatus::CREATED, $webhook->payload->status);
    }

    #[Test]
    public function handlesRefundStatusChangedEvent(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{"refundId":"REF-123","transactionId":"TXN-123","pointId":"30dd1836","status":"CREATED","amount":"50.00"}';
        $headers = $this->createHeaders('PAYMENT.REFUND_STATUS_CHANGED', $rawBody);

        $webhook = $handler->handle($headers, $rawBody);

        $this->assertSame(WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED, $webhook->getEvent());
        $this->assertInstanceOf(RefundStatusChangedPayload::class, $webhook->payload);
        $this->assertSame('REF-123', $webhook->payload->refundId);
        $this->assertSame('TXN-123', $webhook->payload->transactionId);
        $this->assertSame(RefundStatus::CREATED, $webhook->payload->status);
    }

    #[Test]
    public function handlesTransactionBlikStatusChangedEvent(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{"transactionId":"TXN-123","actionId":"action-1","externalStatus":"BLIK_AUTHORIZED","externalId":"ext-123"}';
        $headers = $this->createHeaders('PAYMENT.TRANSACTION_BLIK_STATUS_CHANGED', $rawBody);

        $webhook = $handler->handle($headers, $rawBody);

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_BLIK_STATUS_CHANGED, $webhook->getEvent());
        $this->assertInstanceOf(TransactionBlikStatusChangedPayload::class, $webhook->payload);
        $this->assertSame('TXN-123', $webhook->payload->transactionId);
        $this->assertSame('action-1', $webhook->payload->actionId);
        $this->assertSame('BLIK_AUTHORIZED', $webhook->payload->externalStatus);
    }

    /**
     * @return array<string, string>
     */
    private function createHeaders(string $event, string $rawBody): array
    {
        $notificationId = 'notification-123';
        $time = '2024-09-20T09:48:03+02:00';
        $version = '1.2';

        $signatureData = "{$event}|{$version}|{$rawBody}|{$notificationId}|{$time}";
        $signature = base64_encode(hash_hmac('sha512', $signatureData, self::SIGNATURE_KEY, true));

        return [
            'X-Paymentic-Event' => $event,
            'X-Paymentic-Notification-Id' => $notificationId,
            'X-Paymentic-Time' => $time,
            'X-Paymentic-Signature' => $signature,
            'User-Agent' => "Paymentic/{$version}",
        ];
    }
}
