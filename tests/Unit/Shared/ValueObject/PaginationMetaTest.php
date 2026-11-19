<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Shared\ValueObject;

use Paymentic\Sdk\Shared\ValueObject\PaginationMeta;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaginationMetaTest extends TestCase
{
    #[Test]
    public function createsFromArray(): void
    {
        $meta = PaginationMeta::fromArray([
            'currentPage' => 2,
            'perPage' => 50,
            'total' => 120,
            'lastPage' => 3,
        ]);

        $this->assertSame(2, $meta->currentPage);
        $this->assertSame(50, $meta->perPage);
        $this->assertSame(120, $meta->total);
        $this->assertSame(3, $meta->lastPage);
    }
}
