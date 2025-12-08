<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\Enum\CustomerType;
use Paymentic\Sdk\Payment\Domain\Enum\ShippingMethod;
use Paymentic\Sdk\Payment\Domain\ValueObject\Order;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    #[Test]
    public function createsWithAllFields(): void
    {
        $order = new Order(
            id: 'ORDER-123',
            shippingMethod: ShippingMethod::TRACKED_DELIVERY,
            trackingNumber: 'TRACK123456',
            customerType: CustomerType::B2C,
        );

        $this->assertSame('ORDER-123', $order->id);
        $this->assertSame(ShippingMethod::TRACKED_DELIVERY, $order->shippingMethod);
        $this->assertSame('TRACK123456', $order->trackingNumber);
        $this->assertSame(CustomerType::B2C, $order->customerType);
    }

    #[Test]
    public function createsWithNoFields(): void
    {
        $order = new Order();

        $this->assertNull($order->id);
        $this->assertNull($order->shippingMethod);
        $this->assertNull($order->trackingNumber);
        $this->assertNull($order->customerType);
    }

    #[Test]
    public function convertsToArrayWithAllFields(): void
    {
        $order = new Order(
            id: 'ORD-001',
            shippingMethod: ShippingMethod::LOCKER_PICKUP,
            trackingNumber: 'PL123',
            customerType: CustomerType::B2B,
        );

        $array = $order->toArray();

        $this->assertSame('ORD-001', $array['id']);
        $this->assertSame('LOCKER_PICKUP', $array['shippingMethod']);
        $this->assertSame('PL123', $array['trackingNumber']);
        $this->assertSame('B2B', $array['customerType']);
    }

    #[Test]
    public function convertsToArrayExcludingNullFields(): void
    {
        $order = new Order(id: 'ONLY-ID');

        $array = $order->toArray();

        $this->assertSame(['id' => 'ONLY-ID'], $array);
        $this->assertArrayNotHasKey('shippingMethod', $array);
        $this->assertArrayNotHasKey('trackingNumber', $array);
        $this->assertArrayNotHasKey('customerType', $array);
    }

    #[Test]
    public function convertsToEmptyArrayWhenNoFields(): void
    {
        $order = new Order();

        $this->assertSame([], $order->toArray());
    }

    #[Test]
    public function handlesAllShippingMethods(): void
    {
        $methods = [
            ShippingMethod::VIRTUAL,
            ShippingMethod::TRACKED_DELIVERY,
            ShippingMethod::UNTRACKED_DELIVERY,
            ShippingMethod::IN_STORE_PICKUP,
            ShippingMethod::PARCEL_PICKUP,
            ShippingMethod::LOCKER_PICKUP,
            ShippingMethod::HYBRID,
            ShippingMethod::OTHER,
        ];

        foreach ($methods as $method) {
            $order = new Order(shippingMethod: $method);
            $array = $order->toArray();

            $this->assertSame($method->value, $array['shippingMethod']);
        }
    }

    #[Test]
    public function handlesAllCustomerTypes(): void
    {
        $types = [
            CustomerType::B2B,
            CustomerType::B2C,
        ];

        foreach ($types as $type) {
            $order = new Order(customerType: $type);
            $array = $order->toArray();

            $this->assertSame($type->value, $array['customerType']);
        }
    }
}
