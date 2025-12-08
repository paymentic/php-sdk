<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Enum;

enum RefundStatus: string
{
    case CREATED = 'CREATED';
    case ACCEPTED = 'ACCEPTED';
    case PENDING = 'PENDING';
    case DONE = 'DONE';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';
}
