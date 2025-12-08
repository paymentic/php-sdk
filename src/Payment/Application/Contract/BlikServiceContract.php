<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Contract;

use Paymentic\Sdk\Payment\Application\DTO\ProcessBlikRequest;
use Paymentic\Sdk\Payment\Application\DTO\ProcessBlikResponse;

interface BlikServiceContract
{
    public function process(string $pointId, string $transactionId, ProcessBlikRequest $request): ProcessBlikResponse;
}
