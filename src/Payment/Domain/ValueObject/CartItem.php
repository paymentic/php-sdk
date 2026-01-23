<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\Enum\ItemType;
use Paymentic\Sdk\Payment\Domain\Enum\ProductType;
use Paymentic\Sdk\Shared\Exception\InvalidValueException;

final readonly class CartItem
{
    public function __construct(
        public ?string $name = null,
        public ?int $quantity = null,
        public ?string $unitPrice = null,
        public ?ItemType $type = null,
        public ?ProductType $productType = null,
        public ?string $sku = null,
        public ?string $taxRate = null,
        public ?string $taxAmount = null,
        public ?string $url = null,
        public ?string $imageUrl = null,
        public ?string $totalAmount = null,
    ) {
        if (null !== $quantity && $quantity <= 0) {
            throw InvalidValueException::invalidQuantity($quantity);
        }

        if (null !== $unitPrice && (! is_numeric($unitPrice) || (float) $unitPrice < 0)) {
            throw InvalidValueException::invalidAmount($unitPrice);
        }

        if (null !== $taxAmount && (! is_numeric($taxAmount) || (float) $taxAmount < 0)) {
            throw InvalidValueException::invalidAmount($taxAmount);
        }

        if (null !== $totalAmount && (! is_numeric($totalAmount) || (float) $totalAmount < 0)) {
            throw InvalidValueException::invalidAmount($totalAmount);
        }
    }

    /**
     * @return array<string, int|string>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'quantity' => $this->quantity,
            'unitPrice' => $this->unitPrice,
            'type' => $this->type?->value,
            'productType' => $this->productType?->value,
            'sku' => $this->sku,
            'taxRate' => $this->taxRate,
            'taxAmount' => $this->taxAmount,
            'url' => $this->url,
            'imageUrl' => $this->imageUrl,
            'totalAmount' => $this->totalAmount,
        ], static fn ($value) => null !== $value);
    }
}
