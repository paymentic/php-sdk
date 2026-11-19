<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Contract;

use Paymentic\Sdk\Messaging\Application\DTO\PingResponse;

interface SystemServiceContract
{
    public function ping(): PingResponse;
}
