<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Contract;

use Paymentic\Sdk\Messaging\Application\DTO\AddEmailSuppressionsRequest;
use Paymentic\Sdk\Messaging\Application\DTO\AddEmailSuppressionsResponse;
use Paymentic\Sdk\Messaging\Application\DTO\ListEmailSuppressionsRequest;
use Paymentic\Sdk\Messaging\Application\DTO\ListEmailSuppressionsResponse;
use Paymentic\Sdk\Messaging\Domain\Entity\EmailSuppression;

interface EmailSuppressionServiceContract
{
    public function add(AddEmailSuppressionsRequest $request): AddEmailSuppressionsResponse;

    public function list(?ListEmailSuppressionsRequest $request = null): ListEmailSuppressionsResponse;

    public function delete(string $suppressionId): EmailSuppression;
}
