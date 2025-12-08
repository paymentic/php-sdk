<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Webhook\Exception;

use Exception;

final class UnsupportedWebhookEventException extends Exception
{
    public function __construct(string $event)
    {
        parent::__construct(sprintf('Unsupported webhook event: %s', $event));
    }
}
