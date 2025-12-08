<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Laravel;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase;
use Paymentic\Sdk\Integration\Laravel\PaymenticServiceProvider;
use Paymentic\Sdk\Payment\Application\DTO\CreateRefundRequest;
use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionRequest;
use Paymentic\Sdk\Payment\Application\DTO\ProcessBlikRequest;
use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;
use Paymentic\Sdk\PaymenticClient;
use PHPUnit\Framework\Attributes\Test;

final class PaymenticClientIntegrationTest extends TestCase
{
    private PaymenticClient $paymentic;

    protected function getPackageProviders($app): array
    {
        return [PaymenticServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('paymentic.api_key', 'test-api-key');
        $app['config']->set('paymentic.sandbox', true);
    }

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentic = $this->app->make(PaymenticClient::class);
    }

    #[Test]
    public function createsTransaction(): void
    {
        Http::fake([
            '*/transactions' => Http::response([
                'data' => [
                    'id' => 'ABCD-123-XYZ-9876',
                    'redirectUrl' => 'https://pay.paymentic.com/ABCD-123-XYZ-9876',
                    'whitelabel' => null,
                ],
            ], 201),
        ]);

        $request = new CreateTransactionRequest(
            amount: '150.00',
            title: 'Order #999',
        );

        $response = $this->paymentic->payment()->transactions()->create('b8e6e2fc', $request);

        $this->assertSame('ABCD-123-XYZ-9876', $response->id);
        $this->assertStringContainsString('pay.paymentic.com', $response->redirectUrl);
    }

    #[Test]
    public function createsRefund(): void
    {
        Http::fake([
            '*/refunds' => Http::response([
                'data' => [
                    'id' => 'REF-123-456',
                    'status' => 'CREATED',
                ],
            ], 201),
        ]);

        $request = new CreateRefundRequest(
            amount: '50.00',
            reason: 'Customer request',
        );

        $response = $this->paymentic->payment()->refunds()->create('b8e6e2fc', 'TXN-123', $request);

        $this->assertSame('REF-123-456', $response->id);
        $this->assertSame(RefundStatus::CREATED, $response->status);
    }

    #[Test]
    public function processesBlikPayment(): void
    {
        Http::fake([
            '*/blik' => Http::response([
                'data' => [
                    'actionId' => '01kaqf5trc82bk6cqqanjcjwnq',
                    'alias' => null,
                ],
            ], 202),
        ]);

        $request = new ProcessBlikRequest(code: '123456');

        $response = $this->paymentic->payment()->blik()->process('b8e6e2fc', 'TXN-123', $request);

        $this->assertSame('01kaqf5trc82bk6cqqanjcjwnq', $response->actionId);
    }

    #[Test]
    public function requestsAreLoggedForTelescope(): void
    {
        Http::fake(['*' => Http::response([
            'data' => [
                'id' => 'TXN-123',
                'status' => 'PAID',
                'amount' => '100.00',
                'currency' => 'PLN',
                'title' => 'Test',
            ],
        ], 200)]);

        $this->paymentic->payment()->transactions()->get('b8e6e2fc', 'TXN-123');

        Http::assertSentCount(1);
    }
}
