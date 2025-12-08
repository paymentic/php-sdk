<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\DTO;

use Paymentic\Sdk\Payment\Application\DTO\CreateRefundRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateRefundRequestTest extends TestCase
{
    #[Test]
    public function createsWithAllFields(): void
    {
        $request = new CreateRefundRequest(
            amount: '10.00',
            reason: 'Customer requested refund',
            externalReferenceId: 'EXT-REF-123',
        );

        $this->assertSame('10.00', $request->amount);
        $this->assertSame('Customer requested refund', $request->reason);
        $this->assertSame('EXT-REF-123', $request->externalReferenceId);
    }

    #[Test]
    public function createsWithOnlyRequiredFields(): void
    {
        $request = new CreateRefundRequest(amount: '50.00');

        $this->assertSame('50.00', $request->amount);
        $this->assertNull($request->reason);
        $this->assertNull($request->externalReferenceId);
    }

    #[Test]
    public function convertsToArrayWithAllFields(): void
    {
        $request = new CreateRefundRequest(
            amount: '10.00',
            reason: 'Refund reason',
            externalReferenceId: 'REF-123',
        );

        $array = $request->toArray();

        $this->assertSame('10.00', $array['amount']);
        $this->assertSame('Refund reason', $array['reason']);
        $this->assertSame('REF-123', $array['externalReferenceId']);
    }

    #[Test]
    public function convertsToArrayExcludingNullFields(): void
    {
        $request = new CreateRefundRequest(amount: '25.00');

        $array = $request->toArray();

        $this->assertSame(['amount' => '25.00'], $array);
        $this->assertArrayNotHasKey('reason', $array);
        $this->assertArrayNotHasKey('externalReferenceId', $array);
    }
}
