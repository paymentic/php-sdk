<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Symfony\Notifier;

use JsonException;
use Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticTransport;
use Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticTransportFactory;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient as SymfonyMockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Notifier\Exception\IncompleteDsnException;
use Symfony\Component\Notifier\Exception\UnsupportedSchemeException;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Transport;
use Symfony\Component\Notifier\Transport\Dsn;

final class PaymenticTransportFactoryTest extends TestCase
{
    private ?MockHttpClient $psr = null;

    #[Test]
    public function supportsPaymenticScheme(): void
    {
        $this->assertTrue($this->createFactory()->supports(new Dsn('paymentic://KEY@default')));
    }

    #[Test]
    public function doesNotSupportOtherSchemes(): void
    {
        $this->assertFalse($this->createFactory()->supports(new Dsn('other://KEY@default')));
    }

    #[Test]
    public function createsProductionTransport(): void
    {
        $transport = $this->createFactory()->create(new Dsn('paymentic://KEY@default?from=Shop'));

        $this->assertSame('paymentic://api.paymentic.com?from=Shop', (string) $transport);
    }

    #[Test]
    public function createsSandboxTransport(): void
    {
        $transport = $this->createFactory()->create(new Dsn('paymentic://KEY@default?from=Shop&sandbox=1'));

        $this->assertSame('paymentic://api.sandbox.paymentic.com?from=Shop', (string) $transport);
    }

    #[Test]
    public function createsTransportWithoutFrom(): void
    {
        $transport = $this->createFactory()->create(new Dsn('paymentic://KEY@default'));

        $this->assertSame('paymentic://api.paymentic.com', (string) $transport);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function customHostOverridesBaseUrl(): void
    {
        $transport = $this->createFactory()->create(new Dsn('paymentic://KEY@custom.host:8443?from=Shop'));

        $transport->send(new SmsMessage('+48123123123', 'Hi'));

        $this->assertStringStartsWith(
            'https://custom.host:8443/v1_2/messaging/sms/messages',
            (string) $this->psr?->getLastRequest()?->getUri(),
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function rootPathFallsBackToDefaultVersionWithoutDoubleSlashes(): void
    {
        $transport = $this->createFactory()->create(new Dsn('paymentic://KEY@custom.host/'));

        $transport->send(new SmsMessage('+48123123123', 'Hi'));

        $this->assertSame(
            'https://custom.host/v1_2/messaging/sms/messages',
            (string) $this->psr?->getLastRequest()?->getUri(),
        );
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function sandboxBaseUrlIsUsed(): void
    {
        $transport = $this->createFactory()->create(new Dsn('paymentic://KEY@default?sandbox=true'));

        $transport->send(new SmsMessage('+48123123123', 'Hi'));

        $this->assertStringStartsWith(
            'https://api.sandbox.paymentic.com/v1_2',
            (string) $this->psr?->getLastRequest()?->getUri(),
        );
    }

    #[Test]
    public function throwsIncompleteDsnWithoutApiKey(): void
    {
        $this->expectException(IncompleteDsnException::class);

        $this->createFactory()->create(new Dsn('paymentic://default'));
    }

    #[Test]
    public function throwsUnsupportedSchemeForOtherScheme(): void
    {
        $this->expectException(UnsupportedSchemeException::class);

        $this->createFactory()->create(new Dsn('other://KEY@default'));
    }

    #[Test]
    public function worksWithNotifierTransportRegistry(): void
    {
        $transport = (new Transport([$this->createFactory()]))
            ->fromString('paymentic://KEY@default?from=Shop');

        $this->assertInstanceOf(PaymenticTransport::class, $transport);
    }

    #[Test]
    public function usesSymfonyHttpClientWhenNoPsrClientIsInjected(): void
    {
        $requests = [];
        $client = new SymfonyMockHttpClient(static function (string $method, string $url) use (&$requests): MockResponse {
            $requests[] = [$method, $url];

            return new MockResponse((string) json_encode(['data' => [
                'id' => '01JZX8K3M9QABCDEF0123456789', 'status' => 'QUEUED', 'sender' => 'Shop',
                'recipient' => '+48123123123', 'text' => 'Hi', 'segmentsCount' => 1,
                'totalCost' => '0.086', 'source' => 'API', 'priority' => 'MEDIUM',
            ]]), ['http_code' => 202, 'response_headers' => ['Content-Type: application/json']]);
        });

        $transport = (new PaymenticTransportFactory(client: $client))->create(new Dsn('paymentic://KEY@default?from=Shop'));
        $transport->send(new SmsMessage('+48123123123', 'Hi'));

        $this->assertCount(1, $requests);
        $this->assertSame('POST', $requests[0][0]);
        $this->assertStringStartsWith('https://api.paymentic.com/v1_2/messaging/sms', $requests[0][1]);
    }

    private function createFactory(): PaymenticTransportFactory
    {
        $this->psr = new MockHttpClient(json_encode([
            'data' => [
                'id' => '01JZX8K3M9QABCDEF0123456789',
                'status' => 'QUEUED',
                'sender' => 'Shop',
                'recipient' => '+48123123123',
                'text' => 'Hi',
                'segmentsCount' => 1,
                'totalCost' => '0.086',
                'source' => 'API',
                'priority' => 'MEDIUM',
            ],
        ], JSON_THROW_ON_ERROR), 202);

        return new PaymenticTransportFactory(
            client: new SymfonyMockHttpClient(),
            psrClient: $this->psr,
            requestFactory: new MockRequestFactory(),
            streamFactory: new MockStreamFactory(),
        );
    }
}
