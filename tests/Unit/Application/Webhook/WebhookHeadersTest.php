<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\Webhook;

use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use Paymentic\Sdk\Shared\Webhook\WebhookHeaders;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ValueError;

final class WebhookHeadersTest extends TestCase
{
    #[Test]
    public function createsFromArrayWithValidHeaders(): void
    {
        $headers = WebhookHeaders::fromArray([
            'X-Paymentic-Event' => 'PAYMENT.TRANSACTION_STATUS_CHANGED',
            'X-Paymentic-Notification-Id' => '01j96yn02bhbv8j1jjtk36zn2t',
            'X-Paymentic-Time' => '2024-09-20T09:48:03+02:00',
            'X-Paymentic-Signature' => 'test-signature',
            'User-Agent' => 'Paymentic/1.1',
        ]);

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, $headers->event);
        $this->assertSame('01j96yn02bhbv8j1jjtk36zn2t', $headers->notificationId);
        $this->assertSame('2024-09-20T09:48:03+02:00', $headers->time);
        $this->assertSame('test-signature', $headers->signature);
        $this->assertSame('Paymentic/1.1', $headers->userAgent);
    }

    #[Test]
    public function createsFromArrayWithLowercaseHeaders(): void
    {
        $headers = WebhookHeaders::fromArray([
            'x-paymentic-event' => 'PAYMENT.TRANSACTION_STATUS_CHANGED',
            'x-paymentic-notification-id' => '01j96yn02bhbv8j1jjtk36zn2t',
            'x-paymentic-time' => '2024-09-20T09:48:03+02:00',
            'x-paymentic-signature' => 'test-signature',
            'user-agent' => 'Paymentic/1.1',
        ]);

        $this->assertSame(WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED, $headers->event);
    }

    #[Test]
    public function createsWithEnumEvent(): void
    {
        $headers = new WebhookHeaders(
            event: WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED,
            notificationId: 'test-id',
            time: '2024-09-20T09:48:03+02:00',
            signature: 'test-signature',
            userAgent: 'Paymentic/1.1',
        );

        $this->assertSame(WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED, $headers->event);
    }

    #[Test]
    public function createsWithStringEvent(): void
    {
        $headers = new WebhookHeaders(
            event: 'PAYMENT.REFUND_STATUS_CHANGED',
            notificationId: 'test-id',
            time: '2024-09-20T09:48:03+02:00',
            signature: 'test-signature',
            userAgent: 'Paymentic/1.1',
        );

        $this->assertSame(WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED, $headers->event);
    }

    #[Test]
    public function throwsExceptionForInvalidEventString(): void
    {
        $this->expectException(ValueError::class);

        new WebhookHeaders(
            event: 'INVALID_EVENT',
            notificationId: 'test-id',
            time: '2024-09-20T09:48:03+02:00',
            signature: 'test-signature',
            userAgent: 'Paymentic/1.1',
        );
    }

    #[Test]
    public function extractsVersionFromUserAgent(): void
    {
        $headers = new WebhookHeaders(
            event: WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
            notificationId: 'test-id',
            time: '2024-09-20T09:48:03+02:00',
            signature: 'test-signature',
            userAgent: 'Paymentic/1.2',
        );

        $this->assertSame('1.2', $headers->getVersion());
    }

    #[Test]
    public function returnsDefaultVersionForInvalidUserAgent(): void
    {
        $headers = new WebhookHeaders(
            event: WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
            notificationId: 'test-id',
            time: '2024-09-20T09:48:03+02:00',
            signature: 'test-signature',
            userAgent: 'InvalidAgent',
        );

        $this->assertSame('1.2', $headers->getVersion());
    }

    #[Test]
    public function convertsToArray(): void
    {
        $headers = new WebhookHeaders(
            event: WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
            notificationId: '01j96yn02bhbv8j1jjtk36zn2t',
            time: '2024-09-20T09:48:03+02:00',
            signature: 'test-signature',
            userAgent: 'Paymentic/1.2',
        );

        $this->assertSame([
            'x-paymentic-event' => 'PAYMENT.TRANSACTION_STATUS_CHANGED',
            'x-paymentic-notification-id' => '01j96yn02bhbv8j1jjtk36zn2t',
            'x-paymentic-time' => '2024-09-20T09:48:03+02:00',
            'x-paymentic-signature' => 'test-signature',
            'user-agent' => 'Paymentic/1.2',
        ], $headers->toArray());
    }
}
