<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit;

use Paymentic\Sdk\Environment;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    #[Test]
    public function productionHasCorrectValue(): void
    {
        $this->assertSame(
            'PRODUCTION',
            Environment::PRODUCTION->value,
        );
    }

    #[Test]
    public function sandboxHasCorrectValue(): void
    {
        $this->assertSame(
            'SANDBOX',
            Environment::SANDBOX->value,
        );
    }
}
