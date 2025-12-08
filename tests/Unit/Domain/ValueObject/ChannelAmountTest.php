<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelAmount;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChannelAmountTest extends TestCase
{
    #[Test]
    public function createsWithMinimumAndMaximum(): void
    {
        $amount = new ChannelAmount(minimum: '1.00', maximum: '50000.00');

        $this->assertSame('1.00', $amount->minimum);
        $this->assertSame('50000.00', $amount->maximum);
    }

    #[Test]
    public function createsWithSmallAmounts(): void
    {
        $amount = new ChannelAmount(minimum: '0.01', maximum: '0.99');

        $this->assertSame('0.01', $amount->minimum);
        $this->assertSame('0.99', $amount->maximum);
    }

    #[Test]
    public function createsWithLargeAmounts(): void
    {
        $amount = new ChannelAmount(minimum: '1000.00', maximum: '1000000.00');

        $this->assertSame('1000.00', $amount->minimum);
        $this->assertSame('1000000.00', $amount->maximum);
    }
}
