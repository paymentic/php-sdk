<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\Enum\ItemType;
use Paymentic\Sdk\Payment\Domain\Enum\ProductType;
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
            type: ItemType::PRODUCT,
            productType: ProductType::PHYSICAL,
            sku: 'SKU-123',
            taxRate: '23',
            taxAmount: '11.50',
            url: 'https://example.com/product',
            imageUrl: 'https://example.com/image.jpg',
            totalAmount: '99.98',
        );

        $this->assertSame('Product Name', $item->name);
        $this->assertSame(2, $item->quantity);
        $this->assertSame('49.99', $item->unitPrice);
        $this->assertSame(ItemType::PRODUCT, $item->type);
        $this->assertSame(ProductType::PHYSICAL, $item->productType);
        $this->assertSame('SKU-123', $item->sku);
        $this->assertSame('23', $item->taxRate);
        $this->assertSame('11.50', $item->taxAmount);
        $this->assertSame('https://example.com/product', $item->url);
        $this->assertSame('https://example.com/image.jpg', $item->imageUrl);
        $this->assertSame('99.98', $item->totalAmount);
    }

    #[Test]
    public function createsWithNoFields(): void
    {
        $item = new CartItem();

        $this->assertNull($item->name);
        $this->assertNull($item->quantity);
        $this->assertNull($item->unitPrice);
        $this->assertNull($item->type);
        $this->assertNull($item->productType);
        $this->assertNull($item->sku);
        $this->assertNull($item->taxRate);
        $this->assertNull($item->taxAmount);
        $this->assertNull($item->url);
        $this->assertNull($item->imageUrl);
        $this->assertNull($item->totalAmount);
    }

    #[Test]
    public function convertsToArrayWithAllFields(): void
    {
        $item = new CartItem(
            name: 'Test Product',
            quantity: 3,
            unitPrice: '19.99',
            type: ItemType::SHIPPING,
            productType: ProductType::DIGITAL,
            sku: 'SKU-456',
            taxRate: '8',
            taxAmount: '1.60',
            url: 'https://example.com/item',
            imageUrl: 'https://example.com/item.png',
            totalAmount: '21.59',
        );

        $array = $item->toArray();

        $this->assertSame([
            'name' => 'Test Product',
            'quantity' => 3,
            'unitPrice' => '19.99',
            'type' => 'SHIPPING',
            'productType' => 'DIGITAL',
            'sku' => 'SKU-456',
            'taxRate' => '8',
            'taxAmount' => '1.60',
            'url' => 'https://example.com/item',
            'imageUrl' => 'https://example.com/item.png',
            'totalAmount' => '21.59',
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

    #[Test]
    public function throwsExceptionForInvalidTaxAmount(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Amount must be a positive numeric string, got: "invalid"');

        new CartItem(taxAmount: 'invalid');
    }

    #[Test]
    public function throwsExceptionForNegativeTaxAmount(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Amount must be a positive numeric string, got: "-5.00"');

        new CartItem(taxAmount: '-5.00');
    }

    #[Test]
    public function throwsExceptionForInvalidTotalAmount(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Amount must be a positive numeric string, got: "abc"');

        new CartItem(totalAmount: 'abc');
    }

    #[Test]
    public function throwsExceptionForNegativeTotalAmount(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Amount must be a positive numeric string, got: "-100.00"');

        new CartItem(totalAmount: '-100.00');
    }

    #[Test]
    public function acceptsAllItemTypes(): void
    {
        $types = [ItemType::PRODUCT, ItemType::SHIPPING, ItemType::DISCOUNT, ItemType::SURCHARGE, ItemType::GIFT_CARD];

        foreach ($types as $type) {
            $item = new CartItem(type: $type);
            $this->assertSame($type, $item->type);
        }
    }

    #[Test]
    public function acceptsAllProductTypes(): void
    {
        $types = [ProductType::PHYSICAL, ProductType::DIGITAL, ProductType::SERVICE, ProductType::VIRTUAL];

        foreach ($types as $type) {
            $item = new CartItem(productType: $type);
            $this->assertSame($type, $item->productType);
        }
    }

    #[Test]
    public function acceptsValidTaxAmount(): void
    {
        $item = new CartItem(taxAmount: '0.00');

        $this->assertSame('0.00', $item->taxAmount);
    }

    #[Test]
    public function acceptsValidTotalAmount(): void
    {
        $item = new CartItem(totalAmount: '150.50');

        $this->assertSame('150.50', $item->totalAmount);
    }
}
