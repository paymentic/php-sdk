<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Entity;

use DateTimeInterface;
use Paymentic\Sdk\Messaging\Domain\Enum\MessagePriority;
use Paymentic\Sdk\Messaging\Domain\Enum\MessageSource;
use Paymentic\Sdk\Messaging\Domain\Enum\SmsStatus;

final readonly class SmsMessage
{
    public function __construct(
        public string $id,
        public SmsStatus $status,
        public string $recipient,
        public string $text,
        public int $segmentsCount,
        public string $totalCost,
        public MessageSource $source,
        public MessagePriority $priority,
        public ?string $sender = null,
        public ?string $netCost = null,
        public ?string $vatRate = null,
        public ?string $externalReferenceId = null,
        public ?DateTimeInterface $createdAt = null,
        public ?DateTimeInterface $sentAt = null,
        public ?DateTimeInterface $deliveredAt = null,
    ) {
    }
}
