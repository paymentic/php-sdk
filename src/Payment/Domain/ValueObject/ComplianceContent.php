<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

final readonly class ComplianceContent
{
    public function __construct(
        public ?string $text = null,
        public ?string $html = null,
        public ?string $markdown = null,
    ) {
    }
}
