<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Laravel\Notifications;

use Illuminate\Http\Client\Request;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use LogicException;
use Orchestra\Testbench\TestCase;
use Paymentic\Sdk\Integration\Laravel\Notifications\Messages\PaymenticSmsMessage;
use Paymentic\Sdk\Integration\Laravel\Notifications\PaymenticSmsChannel;
use Paymentic\Sdk\Integration\Laravel\PaymenticServiceProvider;
use Paymentic\Tests\Integration\Laravel\Fixtures\PlainNotification;
use Paymentic\Tests\Integration\Laravel\Fixtures\SmsNotification;
use Paymentic\Tests\Integration\Laravel\Fixtures\TestNotifiable;
use PHPUnit\Framework\Attributes\Test;

final class PaymenticSmsChannelTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [PaymenticServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('paymentic.api_key', 'test-api-key');
        $app['config']->set('paymentic.sandbox', true);
    }

    #[Test]
    public function channelManagerResolvesPaymenticSmsDriver(): void
    {
        $driver = $this->app->make(ChannelManager::class)->driver('paymentic-sms');

        $this->assertInstanceOf(PaymenticSmsChannel::class, $driver);
    }

    #[Test]
    public function sendsSmsViaNotifiableRoute(): void
    {
        $this->fakeApi();

        (new TestNotifiable(phone: '+48123123123'))->notify(new SmsNotification());

        Http::assertSent(static fn (Request $request): bool => str_ends_with($request->url(), '/messaging/sms/messages')
            && '+48123123123' === $request['recipient']
            && 'Your code is 1234.' === $request['text']);
    }

    #[Test]
    public function sendsSmsViaOnDemandRouteKeyedByClassName(): void
    {
        $this->fakeApi();

        Notification::route(PaymenticSmsChannel::class, '+48500500500')
            ->notify(new SmsNotification([PaymenticSmsChannel::class]));

        Http::assertSent(static fn (Request $request): bool => '+48500500500' === $request['recipient']);
    }

    #[Test]
    public function sendsSmsViaOnDemandRoute(): void
    {
        $this->fakeApi();

        Notification::route('paymentic-sms', '+48500500500')->notify(new SmsNotification());

        Http::assertSent(static fn (Request $request): bool => '+48500500500' === $request['recipient']);
    }

    #[Test]
    public function usesConfiguredDefaultSender(): void
    {
        $this->app['config']->set('paymentic.messaging.sms.sender', 'Shop');
        $this->fakeApi();

        (new TestNotifiable(phone: '+48123123123'))->notify(new SmsNotification());

        Http::assertSent(static fn (Request $request): bool => 'Shop' === $request['sender']);
    }

    #[Test]
    public function messageSenderOverridesConfig(): void
    {
        $this->app['config']->set('paymentic.messaging.sms.sender', 'Shop');
        $this->fakeApi();

        (new TestNotifiable(phone: '+48123123123'))->notify(
            new SmsNotification(message: PaymenticSmsMessage::create('Hi')->from('Other')),
        );

        Http::assertSent(static fn (Request $request): bool => 'Other' === $request['sender']);
    }

    #[Test]
    public function messageRecipientOverridesRoute(): void
    {
        $this->fakeApi();

        (new TestNotifiable(phone: '+48123123123'))->notify(
            new SmsNotification(message: PaymenticSmsMessage::create('Hi')->to('+48999999999')),
        );

        Http::assertSent(static fn (Request $request): bool => '+48999999999' === $request['recipient']);
    }

    #[Test]
    public function acceptsPlainStringFromToPaymenticSms(): void
    {
        $this->fakeApi();

        (new TestNotifiable(phone: '+48123123123'))->notify(new SmsNotification(message: 'Plain text'));

        Http::assertSent(static fn (Request $request): bool => 'Plain text' === $request['text']);
    }

    #[Test]
    public function passesIdempotencyKeyHeader(): void
    {
        $this->fakeApi();

        (new TestNotifiable(phone: '+48123123123'))->notify(
            new SmsNotification(message: PaymenticSmsMessage::create('Hi')->idempotencyKey('k1')),
        );

        Http::assertSent(static fn (Request $request): bool => $request->hasHeader('X-Idempotency-Key', 'k1'));
    }

    #[Test]
    public function skipsWhenNoRoute(): void
    {
        Http::fake();

        (new TestNotifiable())->notify(new SmsNotification());

        Http::assertNothingSent();
    }

    #[Test]
    public function worksWithClassBasedChannel(): void
    {
        $this->fakeApi();

        (new TestNotifiable(phone: '+48123123123'))->notify(new SmsNotification([PaymenticSmsChannel::class]));

        Http::assertSent(static fn (Request $request): bool => '+48123123123' === $request['recipient']);
    }

    #[Test]
    public function throwsWhenToPaymenticSmsMissing(): void
    {
        $channel = $this->app->make(PaymenticSmsChannel::class);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('is missing the toPaymenticSms() method');

        $channel->send(new TestNotifiable(phone: '+48123123123'), new PlainNotification());
    }

    private function fakeApi(): void
    {
        Http::fake([
            '*/messaging/sms/messages' => Http::response([
                'data' => [
                    'id' => '01JZX8K3M9QABCDEF0123456789',
                    'status' => 'QUEUED',
                    'sender' => 'Shop',
                    'recipient' => '+48123123123',
                    'text' => 'Your code is 1234.',
                    'segmentsCount' => 1,
                    'totalCost' => '0.086',
                    'source' => 'API',
                    'priority' => 'MEDIUM',
                ],
            ], 202),
        ]);
    }
}
