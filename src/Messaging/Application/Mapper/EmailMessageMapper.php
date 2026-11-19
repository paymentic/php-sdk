<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Messaging\Domain\Entity\EmailMessage;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailBounceSubType;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailBounceType;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailFailureCode;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailRecipientStatus;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailRecipientType;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailStatus;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\Enum\MessageSource;
use Paymentic\Sdk\Messaging\Domain\ValueObject\EmailMessageRecipient;

final readonly class EmailMessageMapper
{
    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public static function fromArray(array $data): EmailMessage
    {
        return new EmailMessage(
            id: $data['id'],
            status: EmailStatus::from($data['status']),
            from: $data['from'],
            subject: $data['subject'],
            recipients: array_map(
                static fn (array $recipient): EmailMessageRecipient => self::recipientFromArray($recipient),
                $data['recipients'],
            ),
            recipientsCount: $data['recipientsCount'],
            freeRecipientsCount: $data['freeRecipientsCount'],
            billableRecipientsCount: $data['billableRecipientsCount'],
            attachmentsCount: $data['attachmentsCount'],
            attachmentsBillableMb: $data['attachmentsBillableMb'],
            recipientsCost: $data['recipientsCost'],
            attachmentsCost: $data['attachmentsCost'],
            netCost: $data['netCost'],
            vatRate: $data['vatRate'],
            totalCost: $data['totalCost'],
            source: MessageSource::from($data['source']),
            priority: MessagePriority::from($data['priority']),
            fromName: $data['fromName'] ?? null,
            replyTo: $data['replyTo'] ?? [],
            externalReferenceId: $data['externalReferenceId'] ?? null,
            metadata: $data['metadata'] ?? null,
            failureCode: isset($data['failureCode']) ? EmailFailureCode::from($data['failureCode']) : null,
            createdAt: isset($data['createdAt']) ? new DateTimeImmutable($data['createdAt']) : null,
            sentAt: isset($data['sentAt']) ? new DateTimeImmutable($data['sentAt']) : null,
            failedAt: isset($data['failedAt']) ? new DateTimeImmutable($data['failedAt']) : null,
        );
    }

    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    private static function recipientFromArray(array $data): EmailMessageRecipient
    {
        return new EmailMessageRecipient(
            email: $data['email'],
            type: EmailRecipientType::from($data['type']),
            status: EmailRecipientStatus::from($data['status']),
            isFree: $data['isFree'],
            bounceType: isset($data['bounceType']) ? EmailBounceType::from($data['bounceType']) : null,
            bounceSubType: isset($data['bounceSubType']) ? EmailBounceSubType::from($data['bounceSubType']) : null,
            deliveredAt: isset($data['deliveredAt']) ? new DateTimeImmutable($data['deliveredAt']) : null,
            bouncedAt: isset($data['bouncedAt']) ? new DateTimeImmutable($data['bouncedAt']) : null,
            complainedAt: isset($data['complainedAt']) ? new DateTimeImmutable($data['complainedAt']) : null,
        );
    }
}
