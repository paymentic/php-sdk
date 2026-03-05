<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Contract;

use Paymentic\Sdk\Payment\Application\DTO\PingResponse;

interface SystemServiceContract
{
    public function ping(): PingResponse;
}
