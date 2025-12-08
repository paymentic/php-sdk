<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\DTO;

final readonly class CreateRefundRequest
{
    public function __construct(
        public string $amount,
        public ?string $reason = null,
        public ?string $externalReferenceId = null,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'amount' => $this->amount,
            'reason' => $this->reason,
            'externalReferenceId' => $this->externalReferenceId,
        ], static fn ($value) => null !== $value);
    }
}
