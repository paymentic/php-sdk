<?php

declare(strict_types=1);

namespace Paymentic\Tests\Feature;

use JsonException;
use Paymentic\Sdk\Payment\Domain\Enum\AuthorizationType;
use Paymentic\Sdk\Payment\Domain\Enum\PaymentMethod;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PointFeatureTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[Test]
    public function getsPointChannelsSuccessfully(): void
    {
        $responseBody = json_encode([
            'data' => [
                [
                    'id' => 'mbank',
                    'available' => true,
                    'method' => 'PBL',
                    'name' => 'mBank',
                    'image' => [
                        'default' => 'https://example.com/mbank.png',
                    ],
                    'amount' => [
                        'minimum' => '1.00',
                        'maximum' => '50000.00',
                    ],
                    'currencies' => ['PLN'],
                    'commission' => [
                        'value' => '1.65',
                        'minimum' => '0.30',
                        'fixed' => null,
                    ],
                    'authorization' => [
                        'type' => ['REDIRECT'],
                    ],
                    'paymentType' => 'INSTANT',
                    'enablingAt' => null,
                    'disablingAt' => null,
                ],
                [
                    'id' => 'blik',
                    'available' => true,
                    'method' => 'BLIK',
                    'name' => 'BLIK',
                    'image' => [
                        'default' => 'https://example.com/blik.png',
                    ],
                    'amount' => [
                        'minimum' => '0.01',
                        'maximum' => '10000.00',
                    ],
                    'currencies' => ['PLN'],
                    'commission' => [
                        'value' => '0.90',
                        'minimum' => '0.20',
                        'fixed' => null,
                    ],
                    'authorization' => [
                        'type' => ['MULTI_FACTOR', 'APP_NOTIFICATION'],
                    ],
                    'paymentType' => 'INSTANT',
                    'enablingAt' => null,
                    'disablingAt' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 200);

        $channels = $client->payment()->points()->getChannels('b8e6e2fc');

        $this->assertCount(2, $channels);

        $mbank = $channels[0];
        $this->assertSame('mbank', $mbank->id);
        $this->assertTrue($mbank->available);
        $this->assertSame(PaymentMethod::PBL, $mbank->method);
        $this->assertSame('mBank', $mbank->name);
        $this->assertSame('https://example.com/mbank.png', $mbank->image->default);
        $this->assertSame('1.00', $mbank->amount->minimum);
        $this->assertSame('50000.00', $mbank->amount->maximum);
        $this->assertSame(['PLN'], $mbank->currencies);
        $this->assertSame('1.65', $mbank->commission->value);
        $this->assertSame('0.30', $mbank->commission->minimum);
        $this->assertNull($mbank->commission->fixed);
        $this->assertCount(1, $mbank->authorization->type);
        $this->assertSame(AuthorizationType::REDIRECT, $mbank->authorization->type[0]);
        $this->assertSame('INSTANT', $mbank->paymentType);

        $blik = $channels[1];
        $this->assertSame('blik', $blik->id);
        $this->assertSame(PaymentMethod::BLIK, $blik->method);
        $this->assertCount(2, $blik->authorization->type);
        $this->assertSame(AuthorizationType::MULTI_FACTOR, $blik->authorization->type[0]);
        $this->assertSame(AuthorizationType::APP_NOTIFICATION, $blik->authorization->type[1]);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function getsEmptyChannelsList(): void
    {
        $responseBody = json_encode([
            'data' => [],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 200);

        $channels = $client->payment()->points()->getChannels('b8e6e2fc');

        $this->assertCount(0, $channels);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsNotFoundWhenPointNotFound(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'POINT_NOT_FOUND',
                    'message' => 'Point not found.',
                    'docsUrl' => 'https://docs.paymentic.com/errors#POINT_NOT_FOUND',
                    'details' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 404);

        $this->expectException(NotFoundException::class);

        $client->payment()->points()->getChannels('xxxxxxxx');
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function handlesChannelWithScheduledDates(): void
    {
        $responseBody = json_encode([
            'data' => [
                [
                    'id' => 'new-channel',
                    'available' => false,
                    'method' => 'CARD',
                    'name' => 'New Card Channel',
                    'image' => [
                        'default' => null,
                    ],
                    'amount' => [
                        'minimum' => '5.00',
                        'maximum' => '100000.00',
                    ],
                    'currencies' => ['PLN', 'EUR'],
                    'commission' => [
                        'value' => null,
                        'minimum' => null,
                        'fixed' => '2.50',
                    ],
                    'authorization' => [
                        'type' => ['REDIRECT', 'SCAN_CODE'],
                    ],
                    'paymentType' => 'INSTANT',
                    'enablingAt' => '2025-02-01T00:00:00+00:00',
                    'disablingAt' => '2025-12-31T23:59:59+00:00',
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 200);

        $channels = $client->payment()->points()->getChannels('b8e6e2fc');

        $this->assertCount(1, $channels);
        $channel = $channels[0];

        $this->assertFalse($channel->available);
        $this->assertSame(PaymentMethod::CARD, $channel->method);
        $this->assertSame(['PLN', 'EUR'], $channel->currencies);
        $this->assertNull($channel->commission->value);
        $this->assertSame('2.50', $channel->commission->fixed);
        $this->assertNotNull($channel->enablingAt);
        $this->assertNotNull($channel->disablingAt);
        $this->assertSame('2025-02-01', $channel->enablingAt->format('Y-m-d'));
        $this->assertSame('2025-12-31', $channel->disablingAt->format('Y-m-d'));
    }

    private function createClient(string $responseBody, int $statusCode): PaymenticClient
    {
        return PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient(new MockHttpClient($responseBody, $statusCode))
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();
    }
}
