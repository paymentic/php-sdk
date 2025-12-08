<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit;

use Paymentic\Sdk\Environment;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EnvironmentTest extends TestCase
{
    #[Test]
    public function productionReturnsCorrectBaseUrl(): void
    {
        $this->assertSame(
            'https://api.paymentic.com/v1_2',
            Environment::PRODUCTION->getBaseUrl(),
        );
    }

    #[Test]
    public function sandboxReturnsCorrectBaseUrl(): void
    {
        $this->assertSame(
            'https://api.sandbox.paymentic.com/v1_2',
            Environment::SANDBOX->getBaseUrl(),
        );
    }

    #[Test]
    public function productionHasCorrectValue(): void
    {
        $this->assertSame(
            'https://api.paymentic.com/v1_2',
            Environment::PRODUCTION->value,
        );
    }

    #[Test]
    public function sandboxHasCorrectValue(): void
    {
        $this->assertSame(
            'https://api.sandbox.paymentic.com/v1_2',
            Environment::SANDBOX->value,
        );
    }
}
