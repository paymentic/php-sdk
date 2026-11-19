<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Symfony\Mailer;

use Paymentic\Sdk\Integration\Mailer\PaymenticMailerTransport;
use Paymentic\Sdk\Integration\Symfony\Mailer\PaymenticMailerTransportFactory;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient as SymfonyMockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Mailer\Exception\IncompleteDsnException;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mime\Email;

final class PaymenticMailerTransportFactoryTest extends TestCase
{
    private ?MockHttpClient $psr = null;

    #[Test]
    public function supportsBothSchemes(): void
    {
        $factory = $this->createFactory();

        $this->assertTrue($factory->supports(Dsn::fromString('paymentic+api://KEY@default')));
        $this->assertTrue($factory->supports(Dsn::fromString('paymentic://KEY@default')));
    }

    #[Test]
    public function doesNotSupportOtherSchemes(): void
    {
        $this->assertFalse($this->createFactory()->supports(Dsn::fromString('smtp://KEY@default')));
    }

    #[Test]
    public function createsTransport(): void
    {
        $transport = $this->createFactory()->create(Dsn::fromString('paymentic+api://KEY@default'));

        $this->assertInstanceOf(PaymenticMailerTransport::class, $transport);
    }

    #[Test]
    public function productionByDefault(): void
    {
        $this->createFactory()->create(Dsn::fromString('paymentic+api://KEY@default'))->send($this->email());

        $this->assertSame('https://api.paymentic.com/v1_2/messaging/email/messages', $this->lastUri());
    }

    #[Test]
    public function sandboxOption(): void
    {
        $this->createFactory()->create(Dsn::fromString('paymentic+api://KEY@default?sandbox=1'))->send($this->email());

        $this->assertSame('https://api.sandbox.paymentic.com/v1_2/messaging/email/messages', $this->lastUri());
    }

    #[Test]
    public function sendsApiKeyAsBearerToken(): void
    {
        $this->createFactory()->create(Dsn::fromString('paymentic+api://KEY@default'))->send($this->email());

        $this->assertSame('Bearer KEY', $this->psr?->getLastRequest()?->getHeaderLine('Authorization'));
    }

    #[Test]
    public function decodesApiKeyFromDsn(): void
    {
        $this->createFactory()->create(Dsn::fromString('paymentic+api://K%2BEY@default'))->send($this->email());

        $this->assertSame('Bearer K+EY', $this->psr?->getLastRequest()?->getHeaderLine('Authorization'));
    }

    #[Test]
    public function customHostAndPortOverrideBaseUrl(): void
    {
        $this->createFactory()->create(Dsn::fromString('paymentic+api://KEY@custom.host:8443'))->send($this->email());

        $this->assertSame('https://custom.host:8443/v1_2/messaging/email/messages', $this->lastUri());
    }

    #[Test]
    public function throwsIncompleteDsnWithoutApiKey(): void
    {
        $this->expectException(IncompleteDsnException::class);

        $this->createFactory()->create(Dsn::fromString('paymentic+api://default'));
    }

    #[Test]
    public function throwsUnsupportedSchemeForOtherScheme(): void
    {
        $this->expectException(UnsupportedSchemeException::class);

        $this->createFactory()->create(Dsn::fromString('other://KEY@default'));
    }

    #[Test]
    public function worksWithMailerTransportRegistry(): void
    {
        $transport = (new Transport([$this->createFactory()]))->fromString('paymentic+api://KEY@default?sandbox=1');

        $this->assertInstanceOf(PaymenticMailerTransport::class, $transport);
    }

    #[Test]
    public function usesSymfonyHttpClientWhenNoPsrClientIsInjected(): void
    {
        $requests = [];
        $client = new SymfonyMockHttpClient(static function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url];

            return new MockResponse((string) json_encode(['data' => [
                'id' => '01JZX8K3M9QABCDEF0123456789', 'status' => 'QUEUED', 'from' => 'shop@example.org',
                'subject' => 'Hi', 'recipients' => [], 'recipientsCount' => 1, 'freeRecipientsCount' => 1,
                'billableRecipientsCount' => 0, 'attachmentsCount' => 0, 'attachmentsBillableMb' => 0,
                'recipientsCost' => '0.000', 'attachmentsCost' => '0.000', 'netCost' => '0.000',
                'vatRate' => '23.00', 'totalCost' => '0.000', 'source' => 'API', 'priority' => 'MEDIUM',
            ]]), ['http_code' => 202, 'response_headers' => ['Content-Type: application/json']]);
        });

        $transport = (new PaymenticMailerTransportFactory(client: $client))->create(Dsn::fromString('paymentic+api://KEY@default'));
        $transport->send($this->email());

        $this->assertSame([['POST', 'https://api.paymentic.com/v1_2/messaging/email/messages']], $requests);
    }

    private function email(): Email
    {
        return (new Email())->from('shop@example.org')->to('jan@example.org')->subject('Hi')->text('Body');
    }

    private function lastUri(): string
    {
        $request = $this->psr?->getLastRequest();
        $this->assertNotNull($request);

        return (string) $request->getUri();
    }

    private function createFactory(): PaymenticMailerTransportFactory
    {
        $this->psr = new MockHttpClient(json_encode([
            'data' => [
                'id' => '01JZX8K3M9QABCDEF0123456789',
                'status' => 'QUEUED',
                'from' => 'shop@example.org',
                'subject' => 'Hi',
                'recipients' => [],
                'recipientsCount' => 1,
                'freeRecipientsCount' => 1,
                'billableRecipientsCount' => 0,
                'attachmentsCount' => 0,
                'attachmentsBillableMb' => 0,
                'recipientsCost' => '0.000',
                'attachmentsCost' => '0.000',
                'netCost' => '0.000',
                'vatRate' => '23.00',
                'totalCost' => '0.000',
                'source' => 'API',
                'priority' => 'MEDIUM',
            ],
        ], JSON_THROW_ON_ERROR), 202);

        return new PaymenticMailerTransportFactory(
            psrClient: $this->psr,
            requestFactory: new MockRequestFactory(),
            streamFactory: new MockStreamFactory(),
        );
    }
}
