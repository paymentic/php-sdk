<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Enum;

enum EmailBounceType: string
{
    case PERMANENT = 'PERMANENT';
    case TRANSIENT = 'TRANSIENT';
    case UNDETERMINED = 'UNDETERMINED';
}
