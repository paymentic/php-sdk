<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\DTO;

use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;

final readonly class CreateRefundResponse
{
    public function __construct(
        public string $id,
        public RefundStatus $status,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            status: RefundStatus::from($data['status']),
        );
    }
}
