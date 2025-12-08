<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit;

use Paymentic\Sdk\Environment;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Tests\Support\NoDiscoveryClientFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

final class PaymenticClientFactoryTest extends TestCase
{
    #[Test]
    public function createsFactoryWithApiKey(): void
    {
        $factory = PaymenticClientFactory::create('test-api-key');

        $this->assertInstanceOf(PaymenticClientFactory::class, $factory);
    }

    #[Test]
    public function buildsClientWithPsrInterfaces(): void
    {
        $psrClient = $this->createStub(ClientInterface::class);
        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $streamFactory = $this->createStub(StreamFactoryInterface::class);

        $client = PaymenticClientFactory::create('test-api-key')
            ->withHttpClient($psrClient)
            ->withRequestFactory($requestFactory)
            ->withStreamFactory($streamFactory)
            ->build();

        $this->assertInstanceOf(PaymenticClient::class, $client);
    }

    #[Test]
    public function buildsClientWithSandboxEnvironment(): void
    {
        $psrClient = $this->createStub(ClientInterface::class);
        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $streamFactory = $this->createStub(StreamFactoryInterface::class);

        $client = PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient($psrClient)
            ->withRequestFactory($requestFactory)
            ->withStreamFactory($streamFactory)
            ->build();

        $this->assertInstanceOf(PaymenticClient::class, $client);
    }

    #[Test]
    public function buildsClientWithProductionEnvironment(): void
    {
        $psrClient = $this->createStub(ClientInterface::class);
        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $streamFactory = $this->createStub(StreamFactoryInterface::class);

        $client = PaymenticClientFactory::create('test-api-key')
            ->withProduction()
            ->withHttpClient($psrClient)
            ->withRequestFactory($requestFactory)
            ->withStreamFactory($streamFactory)
            ->build();

        $this->assertInstanceOf(PaymenticClient::class, $client);
    }

    #[Test]
    public function buildsClientWithCustomEnvironment(): void
    {
        $psrClient = $this->createStub(ClientInterface::class);
        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $streamFactory = $this->createStub(StreamFactoryInterface::class);

        $client = PaymenticClientFactory::create('test-api-key')
            ->withEnvironment(Environment::SANDBOX)
            ->withHttpClient($psrClient)
            ->withRequestFactory($requestFactory)
            ->withStreamFactory($streamFactory)
            ->build();

        $this->assertInstanceOf(PaymenticClient::class, $client);
    }

    #[Test]
    public function autoDiscoversGuzzleWhenNotProvided(): void
    {
        $client = PaymenticClientFactory::create('test-api-key')->build();

        $this->assertInstanceOf(PaymenticClient::class, $client);
    }

    #[Test]
    public function autoDiscoversFactoriesWhenOnlyClientProvided(): void
    {
        $psrClient = $this->createStub(ClientInterface::class);

        $client = PaymenticClientFactory::create('test-api-key')
            ->withHttpClient($psrClient)
            ->build();

        $this->assertInstanceOf(PaymenticClient::class, $client);
    }

    #[Test]
    public function supportsFluentInterface(): void
    {
        $psrClient = $this->createStub(ClientInterface::class);
        $requestFactory = $this->createStub(RequestFactoryInterface::class);
        $streamFactory = $this->createStub(StreamFactoryInterface::class);

        $factory = PaymenticClientFactory::create('api-key');

        $this->assertSame($factory, $factory->withEnvironment(Environment::SANDBOX));
        $this->assertSame($factory, $factory->withSandbox());
        $this->assertSame($factory, $factory->withProduction());
        $this->assertSame($factory, $factory->withHttpClient($psrClient));
        $this->assertSame($factory, $factory->withRequestFactory($requestFactory));
        $this->assertSame($factory, $factory->withStreamFactory($streamFactory));
    }

    #[Test]
    public function throwsExceptionWhenHttpClientNotFound(): void
    {
        $factory = NoDiscoveryClientFactory::create('test-api-key');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No PSR-18 HTTP client found');

        $factory->build();
    }

    #[Test]
    public function throwsExceptionWhenRequestFactoryNotFound(): void
    {
        $factory = NoDiscoveryClientFactory::create('test-api-key')
            ->withHttpClient($this->createStub(ClientInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No PSR-17 RequestFactory found');

        $factory->build();
    }

    #[Test]
    public function throwsExceptionWhenStreamFactoryNotFound(): void
    {
        $factory = NoDiscoveryClientFactory::create('test-api-key')
            ->withHttpClient($this->createStub(ClientInterface::class))
            ->withRequestFactory($this->createStub(RequestFactoryInterface::class));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No PSR-17 StreamFactory found');

        $factory->build();
    }
}
