<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Enum;

enum EmailFailureCode: string
{
    case SENDING_PAUSED = 'SENDING_PAUSED';
    case DOMAIN_SENDING_PAUSED = 'DOMAIN_SENDING_PAUSED';
    case PROVIDER_LIMIT = 'PROVIDER_LIMIT';
    case PROVIDER_UNAVAILABLE = 'PROVIDER_UNAVAILABLE';
    case OUTCOME_UNKNOWN = 'OUTCOME_UNKNOWN';
    case REJECTED = 'REJECTED';
    case SEND_ERROR = 'SEND_ERROR';
    case DOMAIN_NOT_VERIFIED = 'DOMAIN_NOT_VERIFIED';
    case ATTACHMENTS_MISSING = 'ATTACHMENTS_MISSING';
    case EXPIRED = 'EXPIRED';
}
