<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Symfony;

use Paymentic\Sdk\Integration\Symfony\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

final class ConfigurationTest extends TestCase
{
    #[Test]
    public function requiresApiKey(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        $this->expectExceptionMessageMatches('/api_key/');

        $this->processConfiguration([]);
    }

    #[Test]
    public function apiKeyCannotBeEmpty(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->processConfiguration(['api_key' => '']);
    }

    #[Test]
    public function sandboxDefaultsToFalse(): void
    {
        $config = $this->processConfiguration(['api_key' => 'test-key']);

        $this->assertFalse($config['sandbox']);
    }

    #[Test]
    public function sandboxCanBeEnabled(): void
    {
        $config = $this->processConfiguration([
            'api_key' => 'test-key',
            'sandbox' => true,
        ]);

        $this->assertTrue($config['sandbox']);
    }

    #[Test]
    public function webhookSecretDefaultsToNull(): void
    {
        $config = $this->processConfiguration(['api_key' => 'test-key']);

        $this->assertNull($config['webhook_secret']);
    }

    #[Test]
    public function webhookSecretCanBeSet(): void
    {
        $config = $this->processConfiguration([
            'api_key' => 'test-key',
            'webhook_secret' => 'whsec_123456',
        ]);

        $this->assertSame('whsec_123456', $config['webhook_secret']);
    }

    #[Test]
    public function acceptsValidConfiguration(): void
    {
        $config = $this->processConfiguration([
            'api_key' => 'pk_live_123456',
            'sandbox' => false,
            'webhook_secret' => 'whsec_abcdef',
        ]);

        $this->assertSame('pk_live_123456', $config['api_key']);
        $this->assertFalse($config['sandbox']);
        $this->assertSame('whsec_abcdef', $config['webhook_secret']);
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private function processConfiguration(array $config): array
    {
        $processor = new Processor();
        $configuration = new Configuration();

        return $processor->processConfiguration($configuration, [$config]);
    }
}
