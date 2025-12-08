<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Laravel;

use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Factory as LaravelHttpFactory;
use Illuminate\Support\ServiceProvider;
use Paymentic\Sdk\Environment;
use Paymentic\Sdk\Payment\Webhook\PaymentWebhookHandlerFactory;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\Shared\Webhook\WebhookHandler;

final class PaymenticServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/paymentic.php', 'paymentic');

        $this->app->singleton(PaymenticClient::class, static function (Application $app): PaymenticClient {
            /** @var array{api_key: string, sandbox: bool, webhook_secret: string} $config */
            $config = $app['config']['paymentic'];
            $environment = $config['sandbox'] ? Environment::SANDBOX : Environment::PRODUCTION;
            $httpFactory = new HttpFactory();

            return new PaymenticClient(
                apiKey: $config['api_key'],
                httpClient: new LaravelPsr18Client($app->make(LaravelHttpFactory::class)),
                requestFactory: $httpFactory,
                streamFactory: $httpFactory,
                environment: $environment,
            );
        });

        $this->app->alias(PaymenticClient::class, 'paymentic');

        $this->app->singleton(WebhookHandler::class, static function (Application $app): WebhookHandler {
            /** @var array{api_key: string, sandbox: bool, webhook_secret: string} $config */
            $config = $app['config']['paymentic'];

            return PaymentWebhookHandlerFactory::create($config['webhook_secret']);
        });

        $this->app->alias(WebhookHandler::class, 'paymentic.webhook');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/paymentic.php' => config_path('paymentic.php'),
            ], 'paymentic-config');
        }
    }
}
