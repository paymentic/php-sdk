<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\DTO;

use Paymentic\Sdk\Payment\Application\DTO\CreateRefundResponse;
use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateRefundResponseTest extends TestCase
{
    #[Test]
    public function createsFromArray(): void
    {
        $data = [
            'id' => 'REF-123-456',
            'status' => 'CREATED',
        ];

        $response = CreateRefundResponse::fromArray($data);

        $this->assertSame('REF-123-456', $response->id);
        $this->assertSame(RefundStatus::CREATED, $response->status);
    }

    #[Test]
    public function createsFromArrayWithDifferentStatuses(): void
    {
        $statuses = [
            'CREATED' => RefundStatus::CREATED,
            'ACCEPTED' => RefundStatus::ACCEPTED,
            'PENDING' => RefundStatus::PENDING,
            'DONE' => RefundStatus::DONE,
            'REJECTED' => RefundStatus::REJECTED,
            'CANCELLED' => RefundStatus::CANCELLED,
        ];

        foreach ($statuses as $stringStatus => $enumStatus) {
            $response = CreateRefundResponse::fromArray([
                'id' => 'REF-001',
                'status' => $stringStatus,
            ]);

            $this->assertSame($enumStatus, $response->status);
        }
    }

    #[Test]
    public function createsDirectly(): void
    {
        $response = new CreateRefundResponse(
            id: 'REF-DIRECT-001',
            status: RefundStatus::DONE,
        );

        $this->assertSame('REF-DIRECT-001', $response->id);
        $this->assertSame(RefundStatus::DONE, $response->status);
    }
}
