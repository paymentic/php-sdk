<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Symfony;

use Paymentic\Sdk\Integration\Symfony\DependencyInjection\PaymenticExtension;
use Paymentic\Sdk\Integration\Symfony\PaymenticBundle;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymenticBundleTest extends TestCase
{
    #[Test]
    public function returnsCorrectPath(): void
    {
        $bundle = new PaymenticBundle();
        $path = $bundle->getPath();

        $this->assertStringEndsWith('Integration/Symfony', $path);
        $this->assertDirectoryExists($path);
    }

    #[Test]
    public function hasCorrectExtensionClass(): void
    {
        $bundle = new PaymenticBundle();
        $extension = $bundle->getContainerExtension();

        $this->assertInstanceOf(PaymenticExtension::class, $extension);
    }

    #[Test]
    public function extensionAliasIsPaymentic(): void
    {
        $bundle = new PaymenticBundle();
        $extension = $bundle->getContainerExtension();

        $this->assertSame('paymentic', $extension->getAlias());
    }
}
