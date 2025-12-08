<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit;

use Paymentic\Sdk\Environment;
use Paymentic\Sdk\Payment\PaymentClient;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Tests\Support\MockPsrClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymenticClientTest extends TestCase
{
    #[Test]
    public function returnsPaymentClient(): void
    {
        $client = $this->createClient();

        $this->assertInstanceOf(PaymentClient::class, $client->payment());
    }

    #[Test]
    public function returnsSamePaymentClientInstance(): void
    {
        $client = $this->createClient();

        $this->assertSame($client->payment(), $client->payment());
    }

    #[Test]
    public function acceptsPsrInterfaces(): void
    {
        $client = $this->createClient();

        $this->assertInstanceOf(PaymentClient::class, $client->payment());
    }

    #[Test]
    public function usesProductionEnvironmentByDefault(): void
    {
        $client = new PaymenticClient(
            apiKey: 'test-api-key',
            httpClient: new MockPsrClient(),
            requestFactory: new MockRequestFactory(),
            streamFactory: new MockStreamFactory(),
        );

        $this->assertInstanceOf(PaymentClient::class, $client->payment());
    }

    #[Test]
    public function usesSandboxEnvironment(): void
    {
        $client = new PaymenticClient(
            apiKey: 'test-api-key',
            httpClient: new MockPsrClient(),
            requestFactory: new MockRequestFactory(),
            streamFactory: new MockStreamFactory(),
            environment: Environment::SANDBOX,
        );

        $this->assertInstanceOf(PaymentClient::class, $client->payment());
    }

    private function createClient(): PaymenticClient
    {
        return new PaymenticClient(
            apiKey: 'test-api-key',
            httpClient: new MockPsrClient(),
            requestFactory: new MockRequestFactory(),
            streamFactory: new MockStreamFactory(),
            environment: Environment::SANDBOX,
        );
    }
}
