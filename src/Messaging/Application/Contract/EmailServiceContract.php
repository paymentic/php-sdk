<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Contract;

use Paymentic\Sdk\Messaging\Application\DTO\SendEmailRequest;
use Paymentic\Sdk\Messaging\Domain\Entity\EmailMessage;

interface EmailServiceContract
{
    public function send(SendEmailRequest $request, ?string $idempotencyKey = null): EmailMessage;

    public function get(string $messageId): EmailMessage;
}
