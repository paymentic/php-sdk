<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Contract;

use Paymentic\Sdk\Payment\Domain\Entity\Channel;

interface PointServiceContract
{
    /**
     * @return Channel[]
     */
    public function getChannels(string $pointId): array;
}
