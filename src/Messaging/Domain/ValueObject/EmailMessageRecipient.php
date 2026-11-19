<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\ValueObject;

use DateTimeInterface;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailBounceSubType;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailBounceType;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailRecipientStatus;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailRecipientType;

final readonly class EmailMessageRecipient
{
    public function __construct(
        public string $email,
        public EmailRecipientType $type,
        public EmailRecipientStatus $status,
        public bool $isFree,
        public ?EmailBounceType $bounceType = null,
        public ?EmailBounceSubType $bounceSubType = null,
        public ?DateTimeInterface $deliveredAt = null,
        public ?DateTimeInterface $bouncedAt = null,
        public ?DateTimeInterface $complainedAt = null,
    ) {
    }
}
