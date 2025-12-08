<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\Enum\AuthorizationType;

final readonly class ChannelAuthorization
{
    /**
     * @param AuthorizationType[] $type
     */
    public function __construct(
        public array $type = [],
    ) {
    }
}
