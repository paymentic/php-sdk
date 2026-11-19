<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Symfony;

use Paymentic\Sdk\Environment;
use Paymentic\Sdk\Integration\Symfony\DependencyInjection\PaymenticExtension;
use Paymentic\Sdk\Integration\Symfony\Mailer\PaymenticMailerTransportFactory;
use Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticTransportFactory;
use Paymentic\Sdk\PaymenticClient;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

final class PaymenticExtensionTest extends TestCase
{
    #[Test]
    public function registersPaymenticClientService(): void
    {
        $container = $this->createContainer([
            'api_key' => 'test-api-key',
            'sandbox' => false,
        ]);

        $this->assertTrue($container->hasDefinition(PaymenticClient::class));
    }

    #[Test]
    public function injectsPsrInterfaces(): void
    {
        $container = $this->createContainer([
            'api_key' => 'test-api-key',
        ]);

        $definition = $container->getDefinition(PaymenticClient::class);
        $arguments = $definition->getArguments();

        $this->assertInstanceOf(Reference::class, $arguments[1]);
        $this->assertSame(ClientInterface::class, (string) $arguments[1]);

        $this->assertInstanceOf(Reference::class, $arguments[2]);
        $this->assertSame(RequestFactoryInterface::class, (string) $arguments[2]);

        $this->assertInstanceOf(Reference::class, $arguments[3]);
        $this->assertSame(StreamFactoryInterface::class, (string) $arguments[3]);
    }

    #[Test]
    public function registersPaymenticAlias(): void
    {
        $container = $this->createContainer([
            'api_key' => 'test-api-key',
        ]);

        $this->assertTrue($container->hasAlias('paymentic'));
    }

    #[Test]
    public function configuresSandboxEnvironment(): void
    {
        $container = $this->createContainer([
            'api_key' => 'test-api-key',
            'sandbox' => true,
        ]);

        $definition = $container->getDefinition(PaymenticClient::class);
        $arguments = $definition->getArguments();

        $this->assertSame(Environment::SANDBOX, $arguments[4]);
    }

    #[Test]
    public function configuresProductionEnvironment(): void
    {
        $container = $this->createContainer([
            'api_key' => 'test-api-key',
            'sandbox' => false,
        ]);

        $definition = $container->getDefinition(PaymenticClient::class);
        $arguments = $definition->getArguments();

        $this->assertSame(Environment::PRODUCTION, $arguments[4]);
    }

    #[Test]
    public function passesApiKeyToClient(): void
    {
        $container = $this->createContainer([
            'api_key' => 'my-secret-key',
        ]);

        $definition = $container->getDefinition(PaymenticClient::class);
        $arguments = $definition->getArguments();

        $this->assertSame('my-secret-key', $arguments[0]);
    }

    #[Test]
    public function registersNotifierTransportFactoryWithTexterTag(): void
    {
        $container = $this->createContainer(['api_key' => 'test-api-key']);

        $this->assertTrue($container->getDefinition(PaymenticTransportFactory::class)->hasTag('texter.transport_factory'));
    }

    #[Test]
    public function transportFactoryReferencesAreOptional(): void
    {
        $container = $this->createContainer(['api_key' => 'test-api-key']);

        $arguments = $container->getDefinition(PaymenticTransportFactory::class)->getArguments();

        $this->assertCount(5, $arguments);
        foreach ($arguments as $argument) {
            $this->assertInstanceOf(Reference::class, $argument);
            $this->assertSame(ContainerInterface::IGNORE_ON_INVALID_REFERENCE, $argument->getInvalidBehavior());
        }
    }

    #[Test]
    public function registersMailerTransportFactoryWithMailerTag(): void
    {
        $container = $this->createContainer(['api_key' => 'test-api-key']);

        $this->assertTrue($container->getDefinition(PaymenticMailerTransportFactory::class)->hasTag('mailer.transport_factory'));
    }

    #[Test]
    public function mailerTransportFactoryReferencesAreOptional(): void
    {
        $container = $this->createContainer(['api_key' => 'test-api-key']);

        $arguments = $container->getDefinition(PaymenticMailerTransportFactory::class)->getArguments();

        $this->assertCount(6, $arguments);
        foreach ($arguments as $argument) {
            $this->assertInstanceOf(Reference::class, $argument);
            $this->assertSame(ContainerInterface::IGNORE_ON_INVALID_REFERENCE, $argument->getInvalidBehavior());
        }
    }

    /**
     * @param array<string, mixed> $config
     */
    private function createContainer(array $config): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $extension = new PaymenticExtension();
        $extension->load([$config], $container);

        return $container;
    }
}
