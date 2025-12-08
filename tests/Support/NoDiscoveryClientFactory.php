<?php

declare(strict_types=1);

namespace Paymentic\Tests\Support;

use Paymentic\Sdk\PaymenticClientFactory;

class NoDiscoveryClientFactory extends PaymenticClientFactory
{
    protected function classExists(string $class): bool
    {
        return false;
    }

    public static function create(string $apiKey): NoDiscoveryClientFactory
    {
        return new self($apiKey);
    }
}
