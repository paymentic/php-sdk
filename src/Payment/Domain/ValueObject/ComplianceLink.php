<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

final readonly class ComplianceLink
{
    public function __construct(
        public ?string $id = null,
        public ?string $label = null,
        public ?string $url = null,
    ) {
    }
}
