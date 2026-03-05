<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\ValueObject;

final readonly class Pagination
{
    public function __construct(
        public int $page,
        public int $pageSize,
        public int $total,
        public int $totalPages,
        public ?int $from = null,
        public ?int $to = null,
        public ?PaginationLinks $links = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            page: $data['page'],
            pageSize: $data['pageSize'],
            total: $data['total'],
            totalPages: $data['totalPages'],
            from: $data['from'] ?? null,
            to: $data['to'] ?? null,
            links: isset($data['links']) ? PaginationLinks::fromArray($data['links']) : null,
        );
    }
}
