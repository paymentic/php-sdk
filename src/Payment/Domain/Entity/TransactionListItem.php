<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\Entity;

use DateTimeInterface;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;

final readonly class TransactionListItem
{
    public function __construct(
        public string $id,
        public TransactionStatus $status,
        public string $amount,
        public string $title,
        public ?string $commission = null,
        public ?string $customerName = null,
        public ?string $customerEmail = null,
        public ?string $externalReferenceId = null,
        public ?string $paymentMethod = null,
        public ?string $paymentChannel = null,
        public ?string $orderId = null,
        public ?string $blikId = null,
        public ?string $cardBin = null,
        public ?DateTimeInterface $paidAt = null,
        public ?DateTimeInterface $createdAt = null,
    ) {
    }
}
