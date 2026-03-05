<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

final readonly class ComplianceItem
{
    /**
     * @param ComplianceLink[] $links
     */
    public function __construct(
        public string $id,
        public string $type,
        public bool $required,
        public ?bool $checked = null,
        public ?ComplianceContent $content = null,
        public array $links = [],
    ) {
    }
}
