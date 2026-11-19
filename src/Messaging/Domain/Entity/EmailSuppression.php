<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Domain\Entity;

use DateTimeInterface;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailSuppressionReason;

final readonly class EmailSuppression
{
    public function __construct(
        public string $id,
        public string $email,
        public EmailSuppressionReason $reason,
        public bool $isAddedByClient,
        public bool $isRemovable,
        public ?DateTimeInterface $createdAt = null,
    ) {
    }
}
