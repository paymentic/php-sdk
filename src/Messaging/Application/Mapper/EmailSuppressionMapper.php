<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Messaging\Domain\Entity\EmailSuppression;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailSuppressionReason;

final readonly class EmailSuppressionMapper
{
    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public static function fromArray(array $data): EmailSuppression
    {
        return new EmailSuppression(
            id: $data['id'],
            email: $data['email'],
            reason: EmailSuppressionReason::from($data['reason']),
            isAddedByClient: $data['isAddedByClient'],
            isRemovable: $data['isRemovable'],
            createdAt: isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $data
     * @return EmailSuppression[]
     * @throws Exception
     */
    public static function fromArrayCollection(array $data): array
    {
        return array_map(
            static fn (array $item): EmailSuppression => self::fromArray($item),
            $data,
        );
    }
}
