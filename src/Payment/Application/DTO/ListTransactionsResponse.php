<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\DTO;

use Paymentic\Sdk\Payment\Domain\Entity\TransactionListItem;
use Paymentic\Sdk\Shared\ValueObject\Pagination;

final readonly class ListTransactionsResponse
{
    /**
     * @param TransactionListItem[] $data
     */
    public function __construct(
        public array $data,
        public Pagination $pagination,
    ) {
    }
}
