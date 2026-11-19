<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Enum;

enum EmailBounceSubType: string
{
    case GENERAL = 'GENERAL';
    case NO_EMAIL = 'NO_EMAIL';
    case SUPPRESSED = 'SUPPRESSED';
    case ON_SUPPRESSION_LIST = 'ON_SUPPRESSION_LIST';
    case MAILBOX_FULL = 'MAILBOX_FULL';
    case MESSAGE_TOO_LARGE = 'MESSAGE_TOO_LARGE';
    case CONTENT_REJECTED = 'CONTENT_REJECTED';
    case ATTACHMENT_REJECTED = 'ATTACHMENT_REJECTED';
    case UNDETERMINED = 'UNDETERMINED';
}
