<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\ValueObject;

final readonly class PaginationMeta
{
    public function __construct(
        public int $currentPage,
        public int $perPage,
        public int $total,
        public int $lastPage,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            currentPage: $data['currentPage'],
            perPage: $data['perPage'],
            total: $data['total'],
            lastPage: $data['lastPage'],
        );
    }
}
