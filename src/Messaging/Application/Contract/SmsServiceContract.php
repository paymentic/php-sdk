<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Contract;

use Paymentic\Sdk\Messaging\Application\DTO\SendSmsRequest;
use Paymentic\Sdk\Messaging\Domain\Entity\SmsMessage;

interface SmsServiceContract
{
    public function send(SendSmsRequest $request, ?string $idempotencyKey = null): SmsMessage;

    public function get(string $messageId): SmsMessage;
}
