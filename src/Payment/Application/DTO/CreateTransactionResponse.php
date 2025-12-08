<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\DTO;

final readonly class CreateTransactionResponse
{
    public function __construct(
        public string $id,
        public string $redirectUrl,
        public mixed $whitelabel = null
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            redirectUrl: $data['redirectUrl'],
            whitelabel: $data['whitelabel'] ?? null,
        );
    }
}
