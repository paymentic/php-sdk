<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\Enum\CustomerType;
use Paymentic\Sdk\Payment\Domain\Enum\ShippingMethod;

final readonly class Order
{
    public function __construct(
        public ?string $id = null,
        public ?ShippingMethod $shippingMethod = null,
        public ?string $trackingNumber = null,
        public ?CustomerType $customerType = null,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'shippingMethod' => $this->shippingMethod?->value,
            'trackingNumber' => $this->trackingNumber,
            'customerType' => $this->customerType?->value,
        ], static fn ($value) => null !== $value);
    }
}
