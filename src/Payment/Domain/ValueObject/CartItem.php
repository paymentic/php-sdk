<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

use Paymentic\Sdk\Shared\Exception\InvalidValueException;

final readonly class CartItem
{
    public function __construct(
        public ?string $name = null,
        public ?int $quantity = null,
        public ?string $unitPrice = null,
    ) {
        if (null !== $quantity && $quantity <= 0) {
            throw InvalidValueException::invalidQuantity($quantity);
        }

        if (null !== $unitPrice && (! is_numeric($unitPrice) || (float) $unitPrice < 0)) {
            throw InvalidValueException::invalidAmount($unitPrice);
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
        ], static fn ($value) => null !== $value);
    }
}
