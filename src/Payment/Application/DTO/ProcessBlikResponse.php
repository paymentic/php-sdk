<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\DTO;

final readonly class ProcessBlikResponse
{
    public function __construct(
        public string $actionId,
        public mixed $alias = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            actionId: $data['actionId'],
            alias: $data['alias'] ?? null,
        );
    }
}
