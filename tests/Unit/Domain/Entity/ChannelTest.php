<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\Entity;

use DateTimeImmutable;
use Paymentic\Sdk\Payment\Domain\Entity\Channel;
use Paymentic\Sdk\Payment\Domain\Enum\AuthorizationType;
use Paymentic\Sdk\Payment\Domain\Enum\PaymentMethod;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelAmount;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelAuthorization;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelCommission;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelImage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChannelTest extends TestCase
{
    #[Test]
    public function createsChannel(): void
    {
        $channel = new Channel(
            id: 'mbank',
            available: true,
            method: PaymentMethod::PBL,
            name: 'mBank',
            image: new ChannelImage(
                default: 'https://example.com/mbank.png',
            ),
            amount: new ChannelAmount(
                minimum: '1.00',
                maximum: '50000.00',
            ),
            currencies: ['PLN'],
            commission: new ChannelCommission(
                value: '1.65',
                minimum: '0.30',
            ),
            authorization: new ChannelAuthorization(
                type: [
                    AuthorizationType::REDIRECT,
                ],
            ),
            paymentType: 'INSTANT',
        );

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

    #[Test]
    public function createsChannelWithScheduledDates(): void
    {
        $enablingAt = new DateTimeImmutable('2025-02-01T00:00:00+00:00');
        $disablingAt = new DateTimeImmutable('2025-12-31T23:59:59+00:00');

        $channel = new Channel(
            id: 'scheduled-channel',
            available: false,
            method: PaymentMethod::CARD,
            name: 'Scheduled Channel',
            image: new ChannelImage(),
            amount: new ChannelAmount(
                minimum: '5.00',
                maximum: '100000.00',
            ),
            currencies: [
                'PLN',
                'EUR',
            ],
            commission: new ChannelCommission(
                fixed: '2.50',
            ),
            authorization: new ChannelAuthorization(
                type: [
                    AuthorizationType::REDIRECT,
                    AuthorizationType::SCAN_CODE,
                ],
            ),
            paymentType: 'INSTANT',
            enablingAt: $enablingAt,
            disablingAt: $disablingAt,
        );

        $this->assertFalse($channel->available);
        $this->assertSame($enablingAt, $channel->enablingAt);
        $this->assertSame($disablingAt, $channel->disablingAt);
        $this->assertSame(['PLN', 'EUR'], $channel->currencies);
    }

    #[Test]
    public function createsChannelWithMultipleCurrencies(): void
    {
        $channel = new Channel(
            id: 'multi-currency',
            available: true,
            method: PaymentMethod::CARD,
            name: 'Multi Currency Channel',
            image: new ChannelImage(),
            amount: new ChannelAmount(
                minimum: '1.00',
                maximum: '10000.00',
            ),
            currencies: [
                'PLN',
                'EUR',
                'USD',
                'GBP',
            ],
            commission: new ChannelCommission(
                value: '2.00',
            ),
            authorization: new ChannelAuthorization(
                type: [
                    AuthorizationType::REDIRECT,
                ],
            ),
            paymentType: 'INSTANT',
        );

        $this->assertCount(4, $channel->currencies);
        $this->assertContains('PLN', $channel->currencies);
        $this->assertContains('EUR', $channel->currencies);
        $this->assertContains('USD', $channel->currencies);
        $this->assertContains('GBP', $channel->currencies);
    }

    #[Test]
    public function createsChannelWithMultipleAuthorizationTypes(): void
    {
        $channel = new Channel(
            id: 'multi-auth',
            available: true,
            method: PaymentMethod::BLIK,
            name: 'Multi Auth Channel',
            image: new ChannelImage(),
            amount: new ChannelAmount(
                minimum: '0.01',
                maximum: '10000.00',
            ),
            currencies: [
                'PLN',
            ],
            commission: new ChannelCommission(
                value: '0.90',
            ),
            authorization: new ChannelAuthorization(type: [
                AuthorizationType::MULTI_FACTOR,
                AuthorizationType::APP_NOTIFICATION,
            ]),
            paymentType: 'INSTANT',
        );

        $this->assertCount(2, $channel->authorization->type);
        $this->assertSame(AuthorizationType::MULTI_FACTOR, $channel->authorization->type[0]);
        $this->assertSame(AuthorizationType::APP_NOTIFICATION, $channel->authorization->type[1]);
    }
}
