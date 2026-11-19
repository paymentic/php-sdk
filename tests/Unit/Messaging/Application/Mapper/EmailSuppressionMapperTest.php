<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Application\Mapper;

use DateTimeImmutable;
use Exception;
use Paymentic\Sdk\Messaging\Application\Mapper\EmailSuppressionMapper;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailSuppressionReason;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class EmailSuppressionMapperTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function mapsSingleItem(): void
    {
        $suppression = EmailSuppressionMapper::fromArray([
            'id' => '01jzx9q4r7mabcdef012345678',
            'email' => 'jan@example.org',
            'reason' => 'COMPLAINT',
            'isAddedByClient' => false,
            'isRemovable' => false,
            'createdAt' => '2026-11-01T10:00:00+00:00',
        ]);

        $this->assertSame('01jzx9q4r7mabcdef012345678', $suppression->id);
        $this->assertSame('jan@example.org', $suppression->email);
        $this->assertSame(EmailSuppressionReason::COMPLAINT, $suppression->reason);
        $this->assertFalse($suppression->isAddedByClient);
        $this->assertFalse($suppression->isRemovable);
        $this->assertInstanceOf(DateTimeImmutable::class, $suppression->createdAt);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsCollection(): void
    {
        $items = EmailSuppressionMapper::fromArrayCollection([
            ['id' => 'ID-1', 'email' => 'a@b.pl', 'reason' => 'MANUAL', 'isAddedByClient' => true, 'isRemovable' => true],
            ['id' => 'ID-2', 'email' => 'c@d.pl', 'reason' => 'BOUNCE', 'isAddedByClient' => false, 'isRemovable' => false],
        ]);

        $this->assertCount(2, $items);
        $this->assertSame('ID-2', $items[1]->id);
        $this->assertNull($items[0]->createdAt);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function mapsEmptyCollection(): void
    {
        $this->assertSame([], EmailSuppressionMapper::fromArrayCollection([]));
    }
}
