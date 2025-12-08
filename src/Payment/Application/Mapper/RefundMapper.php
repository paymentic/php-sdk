<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Payment\Domain\Entity\Refund;
use Paymentic\Sdk\Payment\Domain\Enum\RefundStatus;

final readonly class RefundMapper
{
    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public static function fromArray(array $data): Refund
    {
        return new Refund(
            id: $data['id'],
            status: RefundStatus::from($data['status']),
            amount: $data['amount'],
            reason: $data['reason'] ?? null,
            externalReferenceId: $data['externalReferenceId'] ?? null,
            createdAt: isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null,
            updatedAt: isset($data['updatedAt']) ? new DateTimeImmutable($data['updatedAt']) : null,
        );
    }
}
