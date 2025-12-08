<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\ValueObject\CartItem;
use Paymentic\Sdk\Shared\Exception\InvalidValueException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CartItemTest extends TestCase
{
    #[Test]
    public function createsWithAllFields(): void
    {
        $item = new CartItem(
            name: 'Product Name',
            quantity: 2,
            unitPrice: '49.99',
        );

        $this->assertSame('Product Name', $item->name);
        $this->assertSame(2, $item->quantity);
        $this->assertSame('49.99', $item->unitPrice);
    }

    #[Test]
    public function createsWithNoFields(): void
    {
        $item = new CartItem();

        $this->assertNull($item->name);
        $this->assertNull($item->quantity);
        $this->assertNull($item->unitPrice);
    }

    #[Test]
    public function convertsToArrayWithAllFields(): void
    {
        $item = new CartItem(
            name: 'Test Product',
            quantity: 3,
            unitPrice: '19.99',
        );

        $array = $item->toArray();

        $this->assertSame([
            'name' => 'Test Product',
            'quantity' => 3,
            'unitPrice' => '19.99',
        ], $array);
    }

    #[Test]
    public function convertsToArrayExcludingNullFields(): void
    {
        $item = new CartItem(name: 'Only Name');

        $array = $item->toArray();

        $this->assertSame(['name' => 'Only Name'], $array);
        $this->assertArrayNotHasKey('quantity', $array);
        $this->assertArrayNotHasKey('unitPrice', $array);
    }

    #[Test]
    public function convertsToEmptyArrayWhenNoFields(): void
    {
        $item = new CartItem();

        $this->assertSame([], $item->toArray());
    }

    #[Test]
    public function throwsExceptionForZeroQuantity(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Quantity must be greater than 0, got: 0');

        new CartItem(quantity: 0);
    }

    #[Test]
    public function throwsExceptionForNegativeQuantity(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Quantity must be greater than 0, got: -1');

        new CartItem(quantity: -1);
    }

    #[Test]
    public function throwsExceptionForInvalidUnitPrice(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Amount must be a positive numeric string, got: "invalid"');

        new CartItem(unitPrice: 'invalid');
    }

    #[Test]
    public function throwsExceptionForNegativeUnitPrice(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Amount must be a positive numeric string, got: "-10.00"');

        new CartItem(unitPrice: '-10.00');
    }

    #[Test]
    public function acceptsValidUnitPrice(): void
    {
        $item = new CartItem(unitPrice: '0.00');

        $this->assertSame('0.00', $item->unitPrice);
    }
}
