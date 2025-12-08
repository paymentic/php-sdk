<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

final readonly class ChannelAmount
{
    public function __construct(
        public string $minimum,
        public string $maximum,
    ) {
    }
}
