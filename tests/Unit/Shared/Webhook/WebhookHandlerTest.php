<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Shared\Webhook;

use JsonException;
use Paymentic\Sdk\Shared\Webhook\Exception\InvalidWebhookSignatureException;
use Paymentic\Sdk\Shared\Webhook\Exception\UnsupportedWebhookEventException;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookHandler;
use Paymentic\Sdk\Shared\Webhook\WebhookPayloadInterface;
use Paymentic\Tests\Support\MockWebhookPayload;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class WebhookHandlerTest extends TestCase
{
    private const SIGNATURE_KEY = 'test-secret-key-for-webhook-signature';

    #[Test]
    public function registersPayloadResolver(): void
    {
        $handler = new WebhookHandler(self::SIGNATURE_KEY);

        $result = $handler->registerPayloadResolver(
            WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
            static fn (array $data): WebhookPayloadInterface => MockWebhookPayload::fromArray($data),
        );

        $this->assertSame($handler, $result);
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws InvalidWebhookSignatureException
     * @throws JsonException
     */
    #[Test]
    public function handlesWebhookWithRegisteredResolver(): void
    {
        $handler = new WebhookHandler(self::SIGNATURE_KEY);
        $handler->registerPayloadResolver(
            WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
            static fn (array $data): WebhookPayloadInterface => MockWebhookPayload::fromArray($data),
        );

        $rawBody = '{"transactionId":"TXN-123"}';
        $headers = $this->createValidHeaders('PAYMENT.TRANSACTION_STATUS_CHANGED', $rawBody);

        $webhook = $handler->handle($headers, $rawBody);

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, $webhook->getEvent());
        $this->assertSame('PAYMENT', $webhook->getModule());
    }

    /**
     * @throws InvalidWebhookSignatureException
     * @throws JsonException
     */
    #[Test]
    public function throwsExceptionForUnregisteredEvent(): void
    {
        $handler = new WebhookHandler(self::SIGNATURE_KEY);

        $rawBody = '{}';
        $headers = $this->createValidHeaders('PAYMENT.TRANSACTION_STATUS_CHANGED', $rawBody);

        $this->expectException(UnsupportedWebhookEventException::class);
        $this->expectExceptionMessage('Unsupported webhook event: PAYMENT.TRANSACTION_STATUS_CHANGED');

        $handler->handle($headers, $rawBody);
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws JsonException
     */
    #[Test]
    public function throwsExceptionForInvalidSignature(): void
    {
        $handler = new WebhookHandler(self::SIGNATURE_KEY);
        $handler->registerPayloadResolver(
            WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
            static fn (array $data): WebhookPayloadInterface => MockWebhookPayload::fromArray($data),
        );

        $headers = [
            'X-Paymentic-Event' => 'PAYMENT.TRANSACTION_STATUS_CHANGED',
            'X-Paymentic-Notification-Id' => 'notification-123',
            'X-Paymentic-Time' => '2024-09-20T09:48:03+02:00',
            'X-Paymentic-Signature' => 'invalid-signature',
            'User-Agent' => 'Paymentic/1.2',
        ];

        $this->expectException(InvalidWebhookSignatureException::class);

        $handler->handle($headers, '{}');
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws InvalidWebhookSignatureException
     */
    #[Test]
    public function throwsExceptionForInvalidJson(): void
    {
        $handler = new WebhookHandler(self::SIGNATURE_KEY);
        $handler->registerPayloadResolver(
            WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
            static fn (array $data): WebhookPayloadInterface => MockWebhookPayload::fromArray($data),
        );

        $rawBody = 'invalid-json';
        $headers = $this->createValidHeaders('PAYMENT.TRANSACTION_STATUS_CHANGED', $rawBody);

        $this->expectException(JsonException::class);

        $handler->handle($headers, $rawBody);
    }

    /**
     * @throws UnsupportedWebhookEventException
     * @throws JsonException
     * @throws InvalidWebhookSignatureException
     */
    #[Test]
    public function supportsFluentRegistration(): void
    {
        $handler = (new WebhookHandler(self::SIGNATURE_KEY))
            ->registerPayloadResolver(
                WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
                static fn (array $data): WebhookPayloadInterface => MockWebhookPayload::fromArray($data),
            )
            ->registerPayloadResolver(
                WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED,
                static fn (array $data): WebhookPayloadInterface => MockWebhookPayload::fromArray($data),
            );

        $rawBody = '{"refundId":"REF-123"}';
        $headers = $this->createValidHeaders('PAYMENT.REFUND_STATUS_CHANGED', $rawBody);

        $webhook = $handler->handle($headers, $rawBody);

        $this->assertSame(WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED, $webhook->getEvent());
    }

    /**
     * @return array<string, string>
     */
    private function createValidHeaders(string $event, string $rawBody): array
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
