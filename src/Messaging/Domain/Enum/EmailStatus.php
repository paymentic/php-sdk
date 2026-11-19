<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Enum;

enum EmailStatus: string
{
    case QUEUED = 'QUEUED';
    case SENT = 'SENT';
    case PARTIALLY_DELIVERED = 'PARTIALLY_DELIVERED';
    case DELIVERED = 'DELIVERED';
    case FAILED = 'FAILED';
}
