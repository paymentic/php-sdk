<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Enum;

enum ProductType: string
{
    case PHYSICAL = 'PHYSICAL';
    case DIGITAL = 'DIGITAL';
    case SERVICE = 'SERVICE';
    case VIRTUAL = 'VIRTUAL';
}
