<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Symfony;

use function dirname;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class PaymenticBundle extends Bundle
{
    public function getPath(): string
    {
        return dirname(__DIR__, 2) . '/Integration/Symfony';
    }
}
