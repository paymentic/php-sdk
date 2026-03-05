<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Payment\Domain\Entity\TransactionListItem;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;

final readonly class TransactionListItemMapper
{
    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public static function fromArray(array $data): TransactionListItem
    {
        return new TransactionListItem(
            id: $data['id'],
            status: TransactionStatus::from($data['status']),
            amount: $data['amount'],
            title: $data['title'],
            commission: $data['commission'] ?? null,
            customerName: $data['customerName'] ?? null,
            customerEmail: $data['customerEmail'] ?? null,
            externalReferenceId: $data['externalReferenceId'] ?? null,
            paymentMethod: $data['paymentMethod'] ?? null,
            paymentChannel: $data['paymentChannel'] ?? null,
            orderId: $data['orderId'] ?? null,
            blikId: $data['blikId'] ?? null,
            cardBin: $data['cardBin'] ?? null,
            paidAt: isset($data['paidAt']) ? new DateTimeImmutable($data['paidAt']) : null,
            createdAt: isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @return TransactionListItem[]
     * @throws Exception
     */
    public static function fromArrayCollection(array $data): array
    {
        return array_map(
            static fn (array $item): TransactionListItem => self::fromArray($item),
            $data,
        );
    }
}
