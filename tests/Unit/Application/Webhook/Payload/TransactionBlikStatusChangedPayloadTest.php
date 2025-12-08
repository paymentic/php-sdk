<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\Webhook\Payload;

use Paymentic\Sdk\Payment\Webhook\Payload\TransactionBlikStatusChangedPayload;
use Paymentic\Sdk\Shared\Webhook\WebhookEvent;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TransactionBlikStatusChangedPayloadTest extends TestCase
{
    #[Test]
    public function createsFromArray(): void
    {
        $data = [
            'transactionId' => 'BMBW-7RA-QFB-WAC4',
            'actionId' => '01jzzz3jt9rfja0jygh1nabyf9',
            'externalStatus' => 'BLIK_AUTHORIZED',
            'externalId' => '88329749246',
        ];

        $payload = TransactionBlikStatusChangedPayload::fromArray($data);

        $this->assertSame('BMBW-7RA-QFB-WAC4', $payload->transactionId);
        $this->assertSame('01jzzz3jt9rfja0jygh1nabyf9', $payload->actionId);
        $this->assertSame('BLIK_AUTHORIZED', $payload->externalStatus);
        $this->assertSame('88329749246', $payload->externalId);
    }

    #[Test]
    public function returnsCorrectEventType(): void
    {
        $this->assertSame(
            WebhookEvent::PAYMENT_TRANSACTION_BLIK_STATUS_CHANGED,
            TransactionBlikStatusChangedPayload::getEventType(),
        );
    }
}
