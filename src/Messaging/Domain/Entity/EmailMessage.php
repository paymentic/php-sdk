<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Entity;

use DateTimeInterface;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailFailureCode;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailStatus;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\Enum\MessageSource;
use Paymentic\Sdk\Messaging\Domain\ValueObject\EmailMessageRecipient;

final readonly class EmailMessage
{
    /**
     * @param EmailMessageRecipient[] $recipients
     * @param string[] $replyTo
     * @param array<string, mixed>|null $metadata
     */
    public function __construct(
        public string $id,
        public EmailStatus $status,
        public string $from,
        public string $subject,
        public array $recipients,
        public int $recipientsCount,
        public int $freeRecipientsCount,
        public int $billableRecipientsCount,
        public int $attachmentsCount,
        public int $attachmentsBillableMb,
        public string $recipientsCost,
        public string $attachmentsCost,
        public string $netCost,
        public string $vatRate,
        public string $totalCost,
        public MessageSource $source,
        public MessagePriority $priority,
        public ?string $fromName = null,
        public array $replyTo = [],
        public ?string $externalReferenceId = null,
        public ?array $metadata = null,
        public ?EmailFailureCode $failureCode = null,
        public ?DateTimeInterface $createdAt = null,
        public ?DateTimeInterface $sentAt = null,
        public ?DateTimeInterface $failedAt = null,
    ) {
    }
}
