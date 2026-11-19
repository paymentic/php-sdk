<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Application\DTO;

use Paymentic\Sdk\Messaging\Application\DTO\ListEmailSuppressionsRequest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ListEmailSuppressionsRequestTest extends TestCase
{
    #[Test]
    public function returnsEmptyStringWithoutParameters(): void
    {
        $this->assertSame('', (new ListEmailSuppressionsRequest())->toQueryString());
    }

    #[Test]
    public function buildsQueryStringWithAllFilters(): void
    {
        $request = new ListEmailSuppressionsRequest(
            filterEmail: 'a@b.pl',
            filterReason: 'BOUNCE,MANUAL',
            filterCreatedAtFrom: '2026-11-01',
            filterCreatedAtTo: '2026-11-30',
            pageNumber: 2,
            pageSize: 25,
        );

        $this->assertSame(
            '?filter%5Bemail%5D=a%40b.pl'
            . '&filter%5Breason%5D=BOUNCE%2CMANUAL'
            . '&filter%5BcreatedAt%5D%5Bfrom%5D=2026-11-01'
            . '&filter%5BcreatedAt%5D%5Bto%5D=2026-11-30'
            . '&page%5Bnumber%5D=2'
            . '&page%5Bsize%5D=25',
            $request->toQueryString(),
        );
    }

    #[Test]
    public function buildsQueryStringWithPaginationOnly(): void
    {
        $request = new ListEmailSuppressionsRequest(pageNumber: 1);

        $this->assertSame('?page%5Bnumber%5D=1', $request->toQueryString());
    }
}
