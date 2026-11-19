<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Enum;

enum EmailRecipientStatus: string
{
    case QUEUED = 'QUEUED';
    case SENT = 'SENT';
    case DELIVERED = 'DELIVERED';
    case DELAYED = 'DELAYED';
    case BOUNCED = 'BOUNCED';
    case COMPLAINED = 'COMPLAINED';
    case SUPPRESSED = 'SUPPRESSED';
    case FAILED = 'FAILED';
}
