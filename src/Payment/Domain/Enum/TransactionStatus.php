<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Enum;

enum TransactionStatus: string
{
    case CREATED = 'CREATED';
    case PENDING = 'PENDING';
    case PAID = 'PAID';
    case FAILED = 'FAILED';
    case EXPIRED = 'EXPIRED';
}
