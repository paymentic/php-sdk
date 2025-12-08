<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Payment\Application\Mapper\ChannelMapper;
use Paymentic\Sdk\Payment\Domain\Entity\Channel;
use Paymentic\Sdk\Payment\Domain\Enum\AuthorizationType;
use Paymentic\Sdk\Payment\Domain\Enum\PaymentMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChannelMapperTest extends TestCase
{
    #[Test]
    public function mapsArrayToChannel(): void
    {
        $data = [
            'id' => 'mbank',
            'available' => true,
            'method' => 'PBL',
            'name' => 'mBank',
            'image' => ['default' => 'https://example.com/mbank.png'],
            'amount' => ['minimum' => '1.00', 'maximum' => '50000.00'],
            'currencies' => ['PLN'],
            'commission' => ['value' => '1.65', 'minimum' => '0.30', 'fixed' => null],
            'authorization' => ['type' => ['REDIRECT']],
            'paymentType' => 'INSTANT',
            'enablingAt' => null,
            'disablingAt' => null,
        ];

        $channel = ChannelMapper::fromArray($data);

        $this->assertInstanceOf(Channel::class, $channel);
        $this->assertSame('mbank', $channel->id);
        $this->assertTrue($channel->available);
        $this->assertSame(PaymentMethod::PBL, $channel->method);
        $this->assertSame('mBank', $channel->name);
        $this->assertSame('https://example.com/mbank.png', $channel->image->default);
        $this->assertSame('1.00', $channel->amount->minimum);
        $this->assertSame('50000.00', $channel->amount->maximum);
        $this->assertSame(['PLN'], $channel->currencies);
        $this->assertSame('1.65', $channel->commission->value);
        $this->assertSame('0.30', $channel->commission->minimum);
        $this->assertNull($channel->commission->fixed);
        $this->assertCount(1, $channel->authorization->type);
        $this->assertSame(AuthorizationType::REDIRECT, $channel->authorization->type[0]);
        $this->assertSame('INSTANT', $channel->paymentType);
        $this->assertNull($channel->enablingAt);
        $this->assertNull($channel->disablingAt);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsArrayWithDates(): void
    {
        $data = [
            'id' => 'new-channel',
            'available' => false,
            'method' => 'CARD',
            'name' => 'New Channel',
            'image' => ['default' => null],
            'amount' => ['minimum' => '5.00', 'maximum' => '100000.00'],
            'currencies' => ['PLN', 'EUR'],
            'commission' => ['value' => null, 'minimum' => null, 'fixed' => '2.50'],
            'authorization' => ['type' => ['REDIRECT', 'MULTI_FACTOR']],
            'paymentType' => 'INSTANT',
            'enablingAt' => '2025-02-01T00:00:00+00:00',
            'disablingAt' => '2025-12-31T23:59:59+00:00',
        ];

        $channel = ChannelMapper::fromArray($data);

        $this->assertFalse($channel->available);
        $this->assertSame(PaymentMethod::CARD, $channel->method);
        $this->assertNull($channel->image->default);
        $this->assertSame(['PLN', 'EUR'], $channel->currencies);
        $this->assertNull($channel->commission->value);
        $this->assertSame('2.50', $channel->commission->fixed);
        $this->assertCount(2, $channel->authorization->type);
        $this->assertSame(AuthorizationType::REDIRECT, $channel->authorization->type[0]);
        $this->assertSame(AuthorizationType::MULTI_FACTOR, $channel->authorization->type[1]);
        $this->assertInstanceOf(DateTimeImmutable::class, $channel->enablingAt);
        $this->assertInstanceOf(DateTimeImmutable::class, $channel->disablingAt);
        $this->assertSame('2025-02-01', $channel->enablingAt->format('Y-m-d'));
        $this->assertSame('2025-12-31', $channel->disablingAt->format('Y-m-d'));
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsArrayCollection(): void
    {
        $data = [
            [
                'id' => 'channel-1',
                'available' => true,
                'method' => 'BLIK',
                'name' => 'BLIK',
                'image' => ['default' => null],
                'amount' => ['minimum' => '0.01', 'maximum' => '10000.00'],
                'currencies' => ['PLN'],
                'commission' => ['value' => '0.90', 'minimum' => '0.20', 'fixed' => null],
                'authorization' => ['type' => ['MULTI_FACTOR']],
                'paymentType' => 'INSTANT',
                'enablingAt' => null,
                'disablingAt' => null,
            ],
            [
                'id' => 'channel-2',
                'available' => true,
                'method' => 'PBL',
                'name' => 'Bank Transfer',
                'image' => ['default' => 'https://example.com/pbl.png'],
                'amount' => ['minimum' => '1.00', 'maximum' => '50000.00'],
                'currencies' => ['PLN'],
                'commission' => ['value' => '1.50', 'minimum' => '0.25', 'fixed' => null],
                'authorization' => ['type' => ['REDIRECT']],
                'paymentType' => 'INSTANT',
                'enablingAt' => null,
                'disablingAt' => null,
            ],
        ];

        $channels = ChannelMapper::fromArrayCollection($data);

        $this->assertCount(2, $channels);
        $this->assertSame('channel-1', $channels[0]->id);
        $this->assertSame(PaymentMethod::BLIK, $channels[0]->method);
        $this->assertSame('channel-2', $channels[1]->id);
        $this->assertSame(PaymentMethod::PBL, $channels[1]->method);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsEmptyCollection(): void
    {
        $channels = ChannelMapper::fromArrayCollection([]);

        $this->assertCount(0, $channels);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsAllPaymentMethods(): void
    {
        $methods = ['BLIK', 'PBL', 'BNPL', 'CARD', 'MOBILE_WALLET'];
        $expectedEnums = [
            PaymentMethod::BLIK,
            PaymentMethod::PBL,
            PaymentMethod::BNPL,
            PaymentMethod::CARD,
            PaymentMethod::MOBILE_WALLET,
        ];

        foreach ($methods as $index => $method) {
            $data = $this->createMinimalChannelData(['method' => $method]);
            $channel = ChannelMapper::fromArray($data);

            $this->assertSame($expectedEnums[$index], $channel->method);
        }
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsAllAuthorizationTypes(): void
    {
        $types = ['REDIRECT', 'MULTI_FACTOR', 'SCAN_CODE', 'APP_NOTIFICATION'];
        $expectedEnums = [
            AuthorizationType::REDIRECT,
            AuthorizationType::MULTI_FACTOR,
            AuthorizationType::SCAN_CODE,
            AuthorizationType::APP_NOTIFICATION,
        ];

        $data = $this->createMinimalChannelData([
            'authorization' => ['type' => $types],
        ]);

        $channel = ChannelMapper::fromArray($data);

        $this->assertCount(4, $channel->authorization->type);
        foreach ($expectedEnums as $index => $expectedEnum) {
            $this->assertSame($expectedEnum, $channel->authorization->type[$index]);
        }
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function createMinimalChannelData(array $overrides = []): array
    {
        return array_merge([
            'id' => 'test-channel',
            'available' => true,
            'method' => 'PBL',
            'name' => 'Test Channel',
            'image' => ['default' => null],
            'amount' => ['minimum' => '1.00', 'maximum' => '10000.00'],
            'currencies' => ['PLN'],
            'commission' => ['value' => '1.00', 'minimum' => '0.20', 'fixed' => null],
            'authorization' => ['type' => ['REDIRECT']],
            'paymentType' => 'INSTANT',
            'enablingAt' => null,
            'disablingAt' => null,
        ], $overrides);
    }
}
