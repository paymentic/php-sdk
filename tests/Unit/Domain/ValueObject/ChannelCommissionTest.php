<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelCommission;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChannelCommissionTest extends TestCase
{
    #[Test]
    public function createsWithAllValues(): void
    {
        $commission = new ChannelCommission(
            value: '1.65',
            minimum: '0.30',
            fixed: '2.50',
        );

        $this->assertSame('1.65', $commission->value);
        $this->assertSame('0.30', $commission->minimum);
        $this->assertSame('2.50', $commission->fixed);
    }

    #[Test]
    public function createsWithOnlyValue(): void
    {
        $commission = new ChannelCommission(value: '1.65');

        $this->assertSame('1.65', $commission->value);
        $this->assertNull($commission->minimum);
        $this->assertNull($commission->fixed);
    }

    #[Test]
    public function createsWithOnlyFixed(): void
    {
        $commission = new ChannelCommission(fixed: '2.50');

        $this->assertNull($commission->value);
        $this->assertNull($commission->minimum);
        $this->assertSame('2.50', $commission->fixed);
    }

    #[Test]
    public function createsWithValueAndMinimum(): void
    {
        $commission = new ChannelCommission(value: '0.90', minimum: '0.20');

        $this->assertSame('0.90', $commission->value);
        $this->assertSame('0.20', $commission->minimum);
        $this->assertNull($commission->fixed);
    }

    #[Test]
    public function createsWithNoArguments(): void
    {
        $commission = new ChannelCommission();

        $this->assertNull($commission->value);
        $this->assertNull($commission->minimum);
        $this->assertNull($commission->fixed);
    }
}
