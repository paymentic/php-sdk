<?php

declare(strict_types=1);

namespace Paymentic\Sdk;

enum Environment: string
{
    case PRODUCTION = 'PRODUCTION';
    case SANDBOX = 'SANDBOX';
}
