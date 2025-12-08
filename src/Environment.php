<?php

declare(strict_types=1);

namespace Paymentic\Sdk;

enum Environment: string
{
    case PRODUCTION = 'https://api.paymentic.com/v1_2';
    case SANDBOX = 'https://api.sandbox.paymentic.com/v1_2';

    public function getBaseUrl(): string
    {
        return $this->value;
    }
}
