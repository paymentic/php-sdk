<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\DTO;

use Paymentic\Sdk\Payment\Application\DTO\ListTransactionsRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListTransactionsRequestTest extends TestCase
{
    #[Test]
    public function generatesEmptyQueryStringWhenNoFilters(): void
    {
        $request = new ListTransactionsRequest();

        $this->assertSame('', $request->toQueryString());
    }

    #[Test]
    public function generatesQueryStringWithStatusFilter(): void
    {
        $request = new ListTransactionsRequest(filterStatus: 'PAID');

        $queryString = $request->toQueryString();

        $this->assertStringContainsString('filter%5Bstatus%5D=PAID', $queryString);
        $this->assertStringStartsWith('?', $queryString);
    }

    #[Test]
    public function generatesQueryStringWithPagination(): void
    {
        $request = new ListTransactionsRequest(pageNumber: 2, pageSize: 25);

        $queryString = $request->toQueryString();

        $this->assertStringContainsString('page%5Bnumber%5D=2', $queryString);
        $this->assertStringContainsString('page%5Bsize%5D=25', $queryString);
    }

    #[Test]
    public function generatesQueryStringWithMultipleFilters(): void
    {
        $request = new ListTransactionsRequest(
            filterStatus: 'PAID',
            filterCustomerEmail: 'john@example.com',
            filterProvider: 'blik',
        );

        $queryString = $request->toQueryString();

        $this->assertStringContainsString('filter%5Bstatus%5D=PAID', $queryString);
        $this->assertStringContainsString('filter%5BcustomerEmail%5D=john%40example.com', $queryString);
        $this->assertStringContainsString('filter%5Bprovider%5D=blik', $queryString);
    }

    #[Test]
    public function generatesQueryStringWithQuerySearch(): void
    {
        $request = new ListTransactionsRequest(
            queryFull: 'John',
            queryTitle: 'Order #12345',
        );

        $queryString = $request->toQueryString();

        $this->assertStringContainsString('query%5Bfull%5D=John', $queryString);
        $this->assertStringContainsString('query%5Btitle%5D=Order', $queryString);
    }

    #[Test]
    public function generatesQueryStringWithDateRangeFilter(): void
    {
        $request = new ListTransactionsRequest(
            filterCreatedAt: '2024-01-01T00:00:00Z,2024-12-31T23:59:59Z',
        );

        $queryString = $request->toQueryString();

        $this->assertStringContainsString('filter%5BcreatedAt%5D=', $queryString);
    }

    #[Test]
    public function generatesQueryStringWithAllFilters(): void
    {
        $request = new ListTransactionsRequest(
            filterStatus: 'PAID',
            filterAmount: '100.50',
            filterExternalReferenceId: 'EXT-REF-123',
            filterOrderId: 'ORD-12345',
            filterCustomerName: 'John Doe',
            filterCustomerEmail: 'john@example.com',
            filterBlikId: 'BLIK123',
            filterCardBin: '411111',
            filterProvider: 'blik',
            filterCreatedAt: '2024-01-01T00:00:00Z,2024-12-31T23:59:59Z',
            filterPaidAt: '2024-01-01T00:00:00Z,2024-12-31T23:59:59Z',
            queryFull: 'John',
            queryCustomerName: 'John Doe',
            queryCustomerEmail: 'john@example.com',
            queryTitle: 'Order #12345',
            pageNumber: 1,
            pageSize: 25,
        );

        $queryString = $request->toQueryString();

        $this->assertStringStartsWith('?', $queryString);
        $this->assertStringContainsString('filter%5Bstatus%5D=PAID', $queryString);
        $this->assertStringContainsString('page%5Bnumber%5D=1', $queryString);
        $this->assertStringContainsString('page%5Bsize%5D=25', $queryString);
    }
}
