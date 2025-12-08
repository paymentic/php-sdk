<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Webhook\Exception;

use Exception;

final class InvalidWebhookSignatureException extends Exception
{
    public function __construct(string $message = 'Invalid webhook signature')
    {
        parent::__construct($message);
    }
}
