<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\Mapper;

use Exception;
use Paymentic\Sdk\Payment\Application\Mapper\TransactionMapper;
use Paymentic\Sdk\Payment\Domain\Enum\CustomerType;
use Paymentic\Sdk\Payment\Domain\Enum\ShippingMethod;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\Shared\Enum\Currency;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TransactionMapperTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function mapsBasicTransaction(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'CREATED',
            'amount' => '100.00',
            'currency' => 'PLN',
            'title' => 'Test Order',
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertSame('TXN-123', $transaction->id);
        $this->assertSame(TransactionStatus::CREATED, $transaction->status);
        $this->assertSame('100.00', $transaction->amount);
        $this->assertSame(Currency::PLN, $transaction->currency);
        $this->assertSame('Test Order', $transaction->title);
    }

    #[Test]
    public function mapsOrderWithShippingMethod(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'CREATED',
            'amount' => '100.00',
            'currency' => 'PLN',
            'title' => 'Test Order',
            'order' => [
                'id' => 'ORD-123',
                'shippingMethod' => 'TRACKED_DELIVERY',
            ],
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertNotNull($transaction->order);
        $this->assertSame('ORD-123', $transaction->order->id);
        $this->assertSame(ShippingMethod::TRACKED_DELIVERY, $transaction->order->shippingMethod);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsOrderWithTrackingNumber(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'CREATED',
            'amount' => '100.00',
            'currency' => 'PLN',
            'title' => 'Test Order',
            'order' => [
                'id' => 'ORD-123',
                'trackingNumber' => 'TRACK-456',
            ],
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertNotNull($transaction->order);
        $this->assertSame('TRACK-456', $transaction->order->trackingNumber);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsOrderWithCustomerType(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'CREATED',
            'amount' => '100.00',
            'currency' => 'PLN',
            'title' => 'Test Order',
            'order' => [
                'id' => 'ORD-123',
                'customerType' => 'B2B',
            ],
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertNotNull($transaction->order);
        $this->assertSame(CustomerType::B2B, $transaction->order->customerType);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsOrderWithAllFields(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'PAID',
            'amount' => '250.00',
            'currency' => 'EUR',
            'title' => 'Complete Order',
            'order' => [
                'id' => 'ORD-999',
                'shippingMethod' => 'LOCKER_PICKUP',
                'trackingNumber' => 'PL123456789',
                'customerType' => 'B2C',
            ],
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertNotNull($transaction->order);
        $this->assertSame('ORD-999', $transaction->order->id);
        $this->assertSame(ShippingMethod::LOCKER_PICKUP, $transaction->order->shippingMethod);
        $this->assertSame('PL123456789', $transaction->order->trackingNumber);
        $this->assertSame(CustomerType::B2C, $transaction->order->customerType);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsOrderWithNullOptionalFields(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'CREATED',
            'amount' => '100.00',
            'currency' => 'PLN',
            'title' => 'Test Order',
            'order' => [
                'id' => 'ORD-123',
            ],
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertNotNull($transaction->order);
        $this->assertSame('ORD-123', $transaction->order->id);
        $this->assertNull($transaction->order->shippingMethod);
        $this->assertNull($transaction->order->trackingNumber);
        $this->assertNull($transaction->order->customerType);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsTransactionWithoutOrder(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'CREATED',
            'amount' => '100.00',
            'currency' => 'PLN',
            'title' => 'Test Order',
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertNull($transaction->order);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsTransactionWithDates(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'PAID',
            'amount' => '100.00',
            'currency' => 'PLN',
            'title' => 'Test Order',
            'capturedAt' => '2024-01-15T10:30:00+00:00',
            'paidAt' => '2024-01-15T10:31:00+00:00',
            'createdAt' => '2024-01-15T10:00:00+00:00',
            'expiresAt' => '2024-01-16T10:00:00+00:00',
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertNotNull($transaction->capturedAt);
        $this->assertNotNull($transaction->paidAt);
        $this->assertNotNull($transaction->createdAt);
        $this->assertNotNull($transaction->expiresAt);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsTransactionWithCustomer(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'CREATED',
            'amount' => '100.00',
            'currency' => 'PLN',
            'title' => 'Test Order',
            'customer' => [
                'email' => 'test@example.com',
                'name' => 'John Doe',
            ],
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertNotNull($transaction->customer);
        $this->assertSame('test@example.com', $transaction->customer->email);
        $this->assertSame('John Doe', $transaction->customer->name);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsTransactionWithCart(): void
    {
        $data = [
            'id' => 'TXN-123',
            'status' => 'CREATED',
            'amount' => '100.00',
            'currency' => 'PLN',
            'title' => 'Test Order',
            'cart' => [
                ['name' => 'Product 1', 'quantity' => 2, 'unitPrice' => '50.00'],
            ],
        ];

        $transaction = TransactionMapper::fromArray($data);

        $this->assertNotNull($transaction->cart);
        $this->assertCount(1, $transaction->cart);
        $this->assertSame('Product 1', $transaction->cart[0]->name);
    }
}
