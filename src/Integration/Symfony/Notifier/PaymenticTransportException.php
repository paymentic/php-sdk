<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Symfony\Notifier;

use Symfony\Component\Notifier\Exception\RuntimeException;
use Symfony\Component\Notifier\Exception\TransportExceptionInterface;
use Throwable;

final class PaymenticTransportException extends RuntimeException implements TransportExceptionInterface
{
    public function __construct(string $message, int $code = 0, ?Throwable $previous = null, private readonly string $debug = '')
    {
        parent::__construct($message, $code, $previous);
    }

    public function getDebug(): string
    {
        return $this->debug;
    }
}
