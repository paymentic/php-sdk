<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\Webhook\Payload;

use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\Payment\Webhook\Payload\TransactionStatusChangedPayload;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TransactionStatusChangedPayloadTest extends TestCase
{
    #[Test]
    public function createsFromArray(): void
    {
        $data = [
            'transactionId' => 'FJRS-LY7-3W0-30K9',
            'pointId' => '000cb241',
            'status' => 'PAID',
            'amount' => '10.23',
            'currency' => 'PLN',
            'commission' => '0.50',
            'externalReferenceId' => 'EXT-123',
            'paymentMethod' => 'BLIK',
            'paymentChannel' => 'blik',
        ];

        $payload = TransactionStatusChangedPayload::fromArray($data);

        $this->assertSame('FJRS-LY7-3W0-30K9', $payload->transactionId);
        $this->assertSame('000cb241', $payload->pointId);
        $this->assertSame(TransactionStatus::PAID, $payload->status);
        $this->assertSame('10.23', $payload->amount);
        $this->assertSame('PLN', $payload->currency);
        $this->assertSame('0.50', $payload->commission);
        $this->assertSame('EXT-123', $payload->externalReferenceId);
        $this->assertSame('BLIK', $payload->paymentMethod);
        $this->assertSame('blik', $payload->paymentChannel);
    }

    #[Test]
    public function createsFromArrayWithNullableFields(): void
    {
        $data = [
            'transactionId' => 'FJRS-LY7-3W0-30K9',
            'pointId' => '000cb241',
            'status' => 'CREATED',
            'amount' => '10.00',
            'currency' => 'PLN',
        ];

        $payload = TransactionStatusChangedPayload::fromArray($data);

        $this->assertNull($payload->commission);
        $this->assertNull($payload->externalReferenceId);
        $this->assertNull($payload->paymentMethod);
        $this->assertNull($payload->paymentChannel);
    }

    #[Test]
    public function returnsCorrectEventType(): void
    {
        $this->assertSame(
            WebhookEvent::PAYMENT_TRANSACTION_STATUS_CHANGED,
            TransactionStatusChangedPayload::getEventType(),
        );
    }
}
