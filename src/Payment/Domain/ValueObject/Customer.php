<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

use Paymentic\Sdk\Shared\Exception\InvalidValueException;

final readonly class Customer
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?bool $emailVerified = null,
        public ?string $phone = null,
        public ?bool $phoneVerified = null,
        public ?string $country = null,
        public ?string $locale = null,
        public ?string $ip = null,
        public ?string $userAgent = null,
        public ?string $fingerprint = null,
    ) {
        if (null !== $email && false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidValueException::invalidEmail($email);
        }

        if (null !== $ip && false === filter_var($ip, FILTER_VALIDATE_IP)) {
            throw InvalidValueException::invalidIp($ip);
        }

        if (null !== $country && (strlen($country) !== 2 || ! ctype_alpha($country))) {
            throw InvalidValueException::invalidCountryCode($country);
        }
    }

    /**
     * @return array<string, string|bool|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'email' => $this->email,
            'emailVerified' => $this->emailVerified,
            'phone' => $this->phone,
            'phoneVerified' => $this->phoneVerified,
            'country' => $this->country,
            'locale' => $this->locale,
            'ip' => $this->ip,
            'userAgent' => $this->userAgent,
            'fingerprint' => $this->fingerprint,
        ], static fn ($value) => null !== $value);
    }
}
