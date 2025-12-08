<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Exception;

use InvalidArgumentException;

final class InvalidValueException extends InvalidArgumentException
{
    public static function invalidEmail(string $email): self
    {
        return new self(sprintf('Invalid email format: "%s"', $email));
    }

    public static function invalidIp(string $ip): self
    {
        return new self(sprintf('Invalid IP address: "%s"', $ip));
    }

    public static function invalidUrl(string $url, string $field): self
    {
        return new self(sprintf('Invalid URL for %s: "%s"', $field, $url));
    }

    public static function invalidCountryCode(string $country): self
    {
        return new self(sprintf('Invalid country code (expected ISO 3166-1 alpha-2): "%s"', $country));
    }

    public static function invalidQuantity(int $quantity): self
    {
        return new self(sprintf('Quantity must be greater than 0, got: %d', $quantity));
    }

    public static function invalidAmount(string $amount): self
    {
        return new self(sprintf('Amount must be a positive numeric string, got: "%s"', $amount));
    }
}
