<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelImage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChannelImageTest extends TestCase
{
    #[Test]
    public function createsWithDefaultUrl(): void
    {
        $image = new ChannelImage(default: 'https://example.com/channel.png');

        $this->assertSame('https://example.com/channel.png', $image->default);
    }

    #[Test]
    public function createsWithNullDefault(): void
    {
        $image = new ChannelImage(default: null);

        $this->assertNull($image->default);
    }

    #[Test]
    public function createsWithNoArguments(): void
    {
        $image = new ChannelImage();

        $this->assertNull($image->default);
    }
}
