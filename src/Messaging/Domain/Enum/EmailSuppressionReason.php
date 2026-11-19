<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Enum;

enum EmailSuppressionReason: string
{
    case BOUNCE = 'BOUNCE';
    case COMPLAINT = 'COMPLAINT';
    case MANUAL = 'MANUAL';
    case UNSUBSCRIBE = 'UNSUBSCRIBE';
}
