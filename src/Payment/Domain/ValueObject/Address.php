<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

use Paymentic\Sdk\Shared\Exception\InvalidValueException;

final readonly class Address
{
    public function __construct(
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $street = null,
        public ?string $buildingNumber = null,
        public ?string $flat = null,
        public ?string $city = null,
        public ?string $region = null,
        public ?string $postalCode = null,
        public ?string $state = null,
        public ?string $country = null,
        public ?string $company = null,
        public ?string $phone = null,
        public ?string $birthDate = null,
    ) {
        if (null !== $country && (strlen($country) !== 2 || ! ctype_alpha($country))) {
            throw InvalidValueException::invalidCountryCode($country);
        }
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'street' => $this->street,
            'buildingNumber' => $this->buildingNumber,
            'flat' => $this->flat,
            'city' => $this->city,
            'region' => $this->region,
            'postalCode' => $this->postalCode,
            'state' => $this->state,
            'country' => $this->country,
            'company' => $this->company,
            'phone' => $this->phone,
            'birthDate' => $this->birthDate,
        ], static fn ($value) => null !== $value);
    }
}
