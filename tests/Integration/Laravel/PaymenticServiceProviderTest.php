<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Laravel;

use Illuminate\Contracts\Container\BindingResolutionException;
use Orchestra\Testbench\TestCase;
use Paymentic\Sdk\Integration\Laravel\PaymenticServiceProvider;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\Shared\Webhook\WebhookHandler;
use PHPUnit\Framework\Attributes\Test;

final class PaymenticServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [PaymenticServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('paymentic.api_key', 'test-api-key');
        $app['config']->set('paymentic.sandbox', true);
        $app['config']->set('paymentic.webhook_secret', 'test-webhook-secret');
    }

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function registersPaymenticClient(): void
    {
        $client = $this->app->make(PaymenticClient::class);

        $this->assertInstanceOf(PaymenticClient::class, $client);
    }

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function resolvesPaymenticAlias(): void
    {
        $client = $this->app->make('paymentic');

        $this->assertInstanceOf(PaymenticClient::class, $client);
    }

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function clientIsSingleton(): void
    {
        $client1 = $this->app->make(PaymenticClient::class);
        $client2 = $this->app->make(PaymenticClient::class);

        $this->assertSame($client1, $client2);
    }

    #[Test]
    public function loadsConfiguration(): void
    {
        $this->assertSame('test-api-key', config('paymentic.api_key'));
        $this->assertTrue(config('paymentic.sandbox'));
    }

    #[Test]
    public function publishesConfiguration(): void
    {
        $this->artisan('vendor:publish', [
            '--provider' => PaymenticServiceProvider::class,
            '--tag' => 'paymentic-config',
        ])->assertSuccessful();

        $this->assertFileExists(config_path('paymentic.php'));

        @unlink(config_path('paymentic.php'));
    }

    #[Test]
    public function configHasDefaultValues(): void
    {
        $this->app['config']->set('paymentic', require __DIR__ . '/../../../src/Integration/Laravel/config/paymentic.php');

        $this->assertSame('', config('paymentic.api_key'));
        $this->assertFalse(config('paymentic.sandbox'));
        $this->assertSame('', config('paymentic.webhook_secret'));
    }

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function registersWebhookHandler(): void
    {
        $handler = $this->app->make(WebhookHandler::class);

        $this->assertInstanceOf(WebhookHandler::class, $handler);
    }

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function resolvesWebhookHandlerAlias(): void
    {
        $handler = $this->app->make('paymentic.webhook');

        $this->assertInstanceOf(WebhookHandler::class, $handler);
    }

    /**
     * @throws BindingResolutionException
     */
    #[Test]
    public function webhookHandlerIsSingleton(): void
    {
        $handler1 = $this->app->make(WebhookHandler::class);
        $handler2 = $this->app->make(WebhookHandler::class);

        $this->assertSame($handler1, $handler2);
    }
}
