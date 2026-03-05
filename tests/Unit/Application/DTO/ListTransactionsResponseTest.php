<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\DTO;

use Paymentic\Sdk\Payment\Application\DTO\ListTransactionsResponse;
use Paymentic\Sdk\Payment\Domain\Entity\TransactionListItem;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\Shared\ValueObject\Pagination;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListTransactionsResponseTest extends TestCase
{
    #[Test]
    public function createsWithDataAndPagination(): void
    {
        $items = [
            new TransactionListItem(
                id: 'ABCD-123-XYZ-9876',
                status: TransactionStatus::PAID,
                amount: '123.45',
                title: 'Order #12345',
            ),
        ];

        $pagination = new Pagination(
            page: 1,
            pageSize: 25,
            total: 1,
            totalPages: 1,
        );

        $response = new ListTransactionsResponse(
            data: $items,
            pagination: $pagination,
        );

        $this->assertCount(1, $response->data);
        $this->assertSame('ABCD-123-XYZ-9876', $response->data[0]->id);
        $this->assertSame(1, $response->pagination->page);
        $this->assertSame(25, $response->pagination->pageSize);
        $this->assertSame(1, $response->pagination->total);
    }

    #[Test]
    public function createsWithEmptyData(): void
    {
        $pagination = new Pagination(
            page: 1,
            pageSize: 25,
            total: 0,
            totalPages: 0,
        );

        $response = new ListTransactionsResponse(
            data: [],
            pagination: $pagination,
        );

        $this->assertCount(0, $response->data);
        $this->assertSame(0, $response->pagination->total);
    }
}
