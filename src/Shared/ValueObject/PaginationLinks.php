<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\ValueObject;

final readonly class PaginationLinks
{
    public function __construct(
        public ?string $first = null,
        public ?string $prev = null,
        public ?string $next = null,
        public ?string $last = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            first: $data['first'] ?? null,
            prev: $data['prev'] ?? null,
            next: $data['next'] ?? null,
            last: $data['last'] ?? null,
        );
    }
}
