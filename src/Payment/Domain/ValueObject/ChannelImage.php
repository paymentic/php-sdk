<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

final readonly class ChannelImage
{
    public function __construct(
        public ?string $default = null,
    ) {
    }
}
