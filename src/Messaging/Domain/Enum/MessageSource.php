<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Enum;

enum MessageSource: string
{
    case WEB = 'WEB';
    case API = 'API';
    case SYSTEM = 'SYSTEM';
}
