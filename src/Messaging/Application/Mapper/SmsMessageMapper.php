<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Messaging\Domain\Entity\SmsMessage;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\Enum\MessageSource;
use Paymentic\Sdk\Messaging\Domain\Enum\SmsStatus;

final readonly class SmsMessageMapper
{
    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public static function fromArray(array $data): SmsMessage
    {
        return new SmsMessage(
            id: $data['id'],
            status: SmsStatus::from($data['status']),
            recipient: $data['recipient'],
            text: $data['text'],
            segmentsCount: $data['segmentsCount'],
            totalCost: $data['totalCost'],
            source: MessageSource::from($data['source']),
            priority: MessagePriority::from($data['priority']),
            sender: $data['sender'] ?? null,
            netCost: $data['netCost'] ?? null,
            vatRate: $data['vatRate'] ?? null,
            externalReferenceId: $data['externalReferenceId'] ?? null,
            createdAt: isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null,
            sentAt: isset($data['sentAt']) ? new DateTimeImmutable($data['sentAt']) : null,
            deliveredAt: isset($data['deliveredAt']) ? new DateTimeImmutable($data['deliveredAt']) : null,
        );
    }
}
