<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Entity;

use DateTimeInterface;
use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;

final readonly class Refund
{
    public function __construct(
        public string $id,
        public RefundStatus $status,
        public string $amount,
        public ?string $reason = null,
        public ?string $externalReferenceId = null,
        public ?DateTimeInterface $createdAt = null,
        public ?DateTimeInterface $updatedAt = null,
    ) {
    }
}
