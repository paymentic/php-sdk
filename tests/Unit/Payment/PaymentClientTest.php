<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Payment;

use Paymentic\Sdk\Payment\Application\Contract\BlikServiceContract;
use Paymentic\Sdk\Payment\Application\Contract\PointServiceContract;
use Paymentic\Sdk\Payment\Application\Contract\RefundServiceContract;
use Paymentic\Sdk\Payment\Application\Contract\TransactionServiceContract;
use Paymentic\Sdk\Payment\PaymentClient;
use Paymentic\Tests\Support\MockHttpClientFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymentClientTest extends TestCase
{
    #[Test]
    public function returnsTransactionService(): void
    {
        $client = new PaymentClient(MockHttpClientFactory::create());

        $this->assertInstanceOf(TransactionServiceContract::class, $client->transactions());
    }

    #[Test]
    public function returnsRefundService(): void
    {
        $client = new PaymentClient(MockHttpClientFactory::create());

        $this->assertInstanceOf(RefundServiceContract::class, $client->refunds());
    }

    #[Test]
    public function returnsBlikService(): void
    {
        $client = new PaymentClient(MockHttpClientFactory::create());

        $this->assertInstanceOf(BlikServiceContract::class, $client->blik());
    }

    #[Test]
    public function returnsPointService(): void
    {
        $client = new PaymentClient(MockHttpClientFactory::create());

        $this->assertInstanceOf(PointServiceContract::class, $client->points());
    }

    #[Test]
    public function returnsSameServiceInstance(): void
    {
        $client = new PaymentClient(MockHttpClientFactory::create());

        $this->assertSame($client->transactions(), $client->transactions());
        $this->assertSame($client->refunds(), $client->refunds());
        $this->assertSame($client->blik(), $client->blik());
        $this->assertSame($client->points(), $client->points());
    }
}
