<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Enum;

enum AuthorizationType: string
{
    case REDIRECT = 'REDIRECT';
    case MULTI_FACTOR = 'MULTI_FACTOR';
    case SCAN_CODE = 'SCAN_CODE';
    case APP_NOTIFICATION = 'APP_NOTIFICATION';
}
