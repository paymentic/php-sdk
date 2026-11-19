<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\DTO;

use Paymentic\Sdk\Messaging\Domain\Entity\EmailSuppression;
use Paymentic\Sdk\Shared\ValueObject\PaginationLinks;
use Paymentic\Sdk\Shared\ValueObject\PaginationMeta;

final readonly class ListEmailSuppressionsResponse
{
    /**
     * @param EmailSuppression[] $data
     */
    public function __construct(
        public array $data,
        public PaginationMeta $meta,
        public ?PaginationLinks $links = null,
    ) {
    }
}
