<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\Webhook;

use JsonException;
use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\Payment\Webhook\Payload\RefundStatusChangedPayload;
use Paymentic\Sdk\Payment\Webhook\Payload\TransactionBlikStatusChangedPayload;
use Paymentic\Sdk\Payment\Webhook\Payload\TransactionStatusChangedPayload;
use Paymentic\Sdk\Payment\Webhook\PaymentWebhookHandlerFactory;
use Paymentic\Sdk\Shared\Webhook\Exception\InvalidWebhookSignatureException;
use Paymentic\Sdk\Shared\Webhook\Exception\UnsupportedWebhookEventException;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookHeaders;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ValueError;

final class WebhookHandlerTest extends TestCase
{
    private const SIGNATURE_KEY = '99ab572393014a7c2f20fe53253fc37819371a033c4507055e94e816683b9c8d';

    /**
     * @throws UnsupportedWebhookEventException
     * @throws InvalidWebhookSignatureException
     * @throws JsonException
     */
    #[Test]
    public function handlesTransactionStatusChangedWebhook(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{"transactionId":"FJRS-LY7-3W0-30K9","pointId":"000cb241","status":"CREATED","amount":"10.00","currency":"PLN","commission":null,"externalReferenceId":null,"paymentMethod":null,"paymentChannel":null}';
        $headers = $this->createHeaders(
            event: 'PAYMENT.TRANSACTION_STATUS_CHANGED',
            rawBody: $rawBody,
        );

        $webhook = $handler->handle($headers, $rawBody);

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, $webhook->getEvent());
        $this->assertInstanceOf(TransactionStatusChangedPayload::class, $webhook->payload);
        $this->assertSame('FJRS-LY7-3W0-30K9', $webhook->payload->transactionId);
        $this->assertSame('000cb241', $webhook->payload->pointId);
        $this->assertSame(TransactionStatus::CREATED, $webhook->payload->status);
        $this->assertSame('10.00', $webhook->payload->amount);
        $this->assertSame('PLN', $webhook->payload->currency);
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws JsonException
     * @throws InvalidWebhookSignatureException
     */
    #[Test]
    public function handlesRefundStatusChangedWebhook(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{"refundId":"REF-LY7-3W0","transactionId":"FJRS-LY7-3W0-30K9","pointId":"000cb241","status":"CREATED","amount":"10.00","externalReferenceId":null}';
        $headers = $this->createHeaders(
            event: 'PAYMENT.REFUND_STATUS_CHANGED',
            rawBody: $rawBody,
        );

        $webhook = $handler->handle($headers, $rawBody);

        $this->assertSame(WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED, $webhook->getEvent());
        $this->assertInstanceOf(RefundStatusChangedPayload::class, $webhook->payload);
        $this->assertSame('REF-LY7-3W0', $webhook->payload->refundId);
        $this->assertSame('FJRS-LY7-3W0-30K9', $webhook->payload->transactionId);
        $this->assertSame('000cb241', $webhook->payload->pointId);
        $this->assertSame(RefundStatus::CREATED, $webhook->payload->status);
        $this->assertSame('10.00', $webhook->payload->amount);
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws InvalidWebhookSignatureException
     * @throws JsonException
     */
    #[Test]
    public function handlesTransactionBlikStatusChangedWebhook(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{"transactionId":"BMBW-7RA-QFB-WAC4","actionId":"01jzzz3jt9rfja0jygh1nabyf9","externalStatus":"BLIK_AUTHORIZED","externalId":"88329749246"}';
        $headers = $this->createHeaders(
            event: 'PAYMENT.TRANSACTION_BLIK_STATUS_CHANGED',
            rawBody: $rawBody,
        );

        $webhook = $handler->handle($headers, $rawBody);

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_BLIK_STATUS_CHANGED, $webhook->getEvent());
        $this->assertInstanceOf(TransactionBlikStatusChangedPayload::class, $webhook->payload);
        $this->assertSame('BMBW-7RA-QFB-WAC4', $webhook->payload->transactionId);
        $this->assertSame('01jzzz3jt9rfja0jygh1nabyf9', $webhook->payload->actionId);
        $this->assertSame('BLIK_AUTHORIZED', $webhook->payload->externalStatus);
        $this->assertSame('88329749246', $webhook->payload->externalId);
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws JsonException
     */
    #[Test]
    public function throwsExceptionForInvalidSignature(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{"transactionId":"FJRS-LY7-3W0-30K9","pointId":"000cb241","status":"CREATED","amount":"10.00","currency":"PLN","commission":null,"externalReferenceId":null,"paymentMethod":null,"paymentChannel":null}';
        $headers = [
            'X-Paymentic-Event' => 'PAYMENT.TRANSACTION_STATUS_CHANGED',
            'X-Paymentic-Notification-Id' => '01j96yn02bhbv8j1jjtk36zn2t',
            'X-Paymentic-Time' => '2024-09-20T09:48:03+02:00',
            'X-Paymentic-Signature' => 'invalid-signature',
            'User-Agent' => 'Paymentic/1.1',
        ];

        $this->expectException(InvalidWebhookSignatureException::class);

        $handler->handle($headers, $rawBody);
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws InvalidWebhookSignatureException
     * @throws JsonException
     */
    #[Test]
    public function throwsExceptionForUnsupportedEvent(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{}';
        $headers = [
            'X-Paymentic-Event' => 'PAYMENT.UNKNOWN_EVENT',
            'X-Paymentic-Notification-Id' => '01j96yn02bhbv8j1jjtk36zn2t',
            'X-Paymentic-Time' => '2024-09-20T09:48:03+02:00',
            'X-Paymentic-Signature' => 'any',
            'User-Agent' => 'Paymentic/1.1',
        ];

        $this->expectException(ValueError::class);

        $handler->handle($headers, $rawBody);
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws InvalidWebhookSignatureException
     */
    #[Test]
    public function throwsExceptionForInvalidJson(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = 'invalid-json';
        $headers = $this->createHeaders(
            event: 'PAYMENT.TRANSACTION_STATUS_CHANGED',
            rawBody: $rawBody,
        );

        $this->expectException(JsonException::class);

        $handler->handle($headers, $rawBody);
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws JsonException
     * @throws InvalidWebhookSignatureException
     */
    #[Test]
    public function verifiesSignatureCorrectly(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{"transactionId":"FJRS-LY7-3W0-30K9","pointId":"000cb241","status":"CREATED","amount":"10.00","currency":"PLN","commission":null,"externalReferenceId":null,"paymentMethod":null,"paymentChannel":null}';
        $notificationId = '01j96yn02bhbv8j1jjtk36zn2t';
        $time = '2024-09-20T09:48:03+02:00';
        $event = 'PAYMENT.TRANSACTION_STATUS_CHANGED';

        $signatureData = "{$event}|1.1|{$rawBody}|{$notificationId}|{$time}";
        $expectedSignature = base64_encode(hash_hmac('sha512', $signatureData, self::SIGNATURE_KEY, true));

        $headers = [
            'X-Paymentic-Event' => $event,
            'X-Paymentic-Notification-Id' => $notificationId,
            'X-Paymentic-Time' => $time,
            'X-Paymentic-Signature' => $expectedSignature,
            'User-Agent' => 'Paymentic/1.1',
        ];

        $webhook = $handler->handle($headers, $rawBody);

        $this->assertSame($notificationId, $webhook->getNotificationId());
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws InvalidWebhookSignatureException
     * @throws JsonException
     */
    #[Test]
    public function acceptsWebhookHeadersObject(): void
    {
        $handler = PaymentWebhookHandlerFactory::create(self::SIGNATURE_KEY);

        $rawBody = '{"transactionId":"FJRS-LY7-3W0-30K9","pointId":"000cb241","status":"CREATED","amount":"10.00","currency":"PLN","commission":null,"externalReferenceId":null,"paymentMethod":null,"paymentChannel":null}';
        $headersArray = $this->createHeaders(
            event: 'PAYMENT.TRANSACTION_STATUS_CHANGED',
            rawBody: $rawBody,
        );

        $webhookHeaders = WebhookHeaders::fromArray($headersArray);

        $webhook = $handler->handle($webhookHeaders, $rawBody);

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, $webhook->getEvent());
        $this->assertInstanceOf(TransactionStatusChangedPayload::class, $webhook->payload);
    }

    /**
     * @return array<string, string>
     */
    private function createHeaders(string $event, string $rawBody): array
    {
        $notificationId = '01j96yn02bhbv8j1jjtk36zn2t';
        $time = '2024-09-20T09:48:03+02:00';
        $version = '1.1';

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
