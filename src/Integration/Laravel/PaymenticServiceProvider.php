<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Laravel;

use GuzzleHttp\Psr7\HttpFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Client\Factory as LaravelHttpFactory;
use Illuminate\Mail\MailManager;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;
use Paymentic\Sdk\Environment;
use Paymentic\Sdk\Integration\Laravel\Notifications\PaymenticSmsChannel;
use Paymentic\Sdk\Integration\Mailer\PaymenticMailerTransport;
use Paymentic\Sdk\Payment\Webhook\PaymentWebhookHandlerFactory;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\Shared\Webhook\WebhookHandler;
use Symfony\Component\Mailer\Transport\AbstractTransport;

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

        $this->registerNotificationChannels();
        $this->registerMailTransport();
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/config/paymentic.php' => config_path('paymentic.php'),
            ], 'paymentic-config');
        }
    }

    private function registerNotificationChannels(): void
    {
        if (! class_exists(ChannelManager::class)) {
            return;
        }

        $this->app->singleton(PaymenticSmsChannel::class, static function (Application $app): PaymenticSmsChannel {
            /** @var string|null $sender */
            $sender = $app['config']['paymentic.messaging.sms.sender'] ?? null;

            return new PaymenticSmsChannel(
                $app->make(PaymenticClient::class)->messaging(),
                '' === $sender ? null : $sender,
            );
        });

        $this->callAfterResolving(ChannelManager::class, static function (ChannelManager $manager): void {
            $manager->extend('paymentic-sms', fn (Application $app): PaymenticSmsChannel => $app->make(PaymenticSmsChannel::class));
        });
    }

    private function registerMailTransport(): void
    {
        if (! class_exists(MailManager::class) || ! class_exists(AbstractTransport::class)) {
            return;
        }

        $this->callAfterResolving('mail.manager', function (MailManager $manager): void {
            $app = $this->app;
            $manager->extend('paymentic', static fn (array $config): PaymenticMailerTransport => new PaymenticMailerTransport(
                $app->make(PaymenticClient::class)->messaging()->email(),
            ));
        });
    }
}
