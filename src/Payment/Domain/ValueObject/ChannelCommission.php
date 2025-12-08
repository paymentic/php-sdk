<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

final readonly class ChannelCommission
{
    public function __construct(
        public ?string $value = null,
        public ?string $minimum = null,
        public ?string $fixed = null,
    ) {
    }
}
