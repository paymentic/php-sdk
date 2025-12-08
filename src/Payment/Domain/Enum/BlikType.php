<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Enum;

enum BlikType: string
{
    case CODE = 'CODE';
    case ALIAS = 'ALIAS';
}
