<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\Webhook\Payload;

use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;
use Paymentic\Sdk\Payment\Webhook\Payload\RefundStatusChangedPayload;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RefundStatusChangedPayloadTest extends TestCase
{
    #[Test]
    public function createsFromArray(): void
    {
        $data = [
            'refundId' => 'REF-LY7-3W0',
            'transactionId' => 'FJRS-LY7-3W0-30K9',
            'pointId' => '000cb241',
            'status' => 'CREATED',
            'amount' => '10.00',
            'externalReferenceId' => 'EXT-REF-123',
        ];

        $payload = RefundStatusChangedPayload::fromArray($data);

        $this->assertSame('REF-LY7-3W0', $payload->refundId);
        $this->assertSame('FJRS-LY7-3W0-30K9', $payload->transactionId);
        $this->assertSame('000cb241', $payload->pointId);
        $this->assertSame(RefundStatus::CREATED, $payload->status);
        $this->assertSame('10.00', $payload->amount);
        $this->assertSame('EXT-REF-123', $payload->externalReferenceId);
    }

    #[Test]
    public function createsFromArrayWithNullExternalReferenceId(): void
    {
        $data = [
            'refundId' => 'REF-LY7-3W0',
            'transactionId' => 'FJRS-LY7-3W0-30K9',
            'pointId' => '000cb241',
            'status' => 'DONE',
            'amount' => '10.00',
        ];

        $payload = RefundStatusChangedPayload::fromArray($data);

        $this->assertNull($payload->externalReferenceId);
        $this->assertSame(RefundStatus::DONE, $payload->status);
    }

    #[Test]
    public function returnsCorrectEventType(): void
    {
        $this->assertSame(
            WebhookEvent::PAYMENT_REFUND_STATUS_CHANGED,
            RefundStatusChangedPayload::getEventType(),
        );
    }
}
