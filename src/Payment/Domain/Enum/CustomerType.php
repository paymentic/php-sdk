<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Enum;

enum CustomerType: string
{
    case B2B = 'B2B';
    case B2C = 'B2C';
}
