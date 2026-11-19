<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Application\DTO;

use Exception;
use Paymentic\Sdk\Messaging\Application\DTO\AddEmailSuppressionsResponse;
use Paymentic\Sdk\Messaging\Domain\Enum\EmailSuppressionReason;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AddEmailSuppressionsResponseTest extends TestCase
{
    /**
     * @throws Exception
     */
    #[Test]
    public function mapsAddedAndExisting(): void
    {
        $response = AddEmailSuppressionsResponse::fromArray([
            'added' => [$this->item('ID-1', 'a@b.pl')],
            'existing' => [$this->item('ID-2', 'c@d.pl'), $this->item('ID-3', 'e@f.pl')],
        ]);

        $this->assertCount(1, $response->added);
        $this->assertSame('a@b.pl', $response->added[0]->email);
        $this->assertSame(EmailSuppressionReason::MANUAL, $response->added[0]->reason);
        $this->assertCount(2, $response->existing);
        $this->assertSame('ID-3', $response->existing[1]->id);
    }

    /**
     * @throws Exception
     */
    #[Test]
    public function defaultsToEmptyLists(): void
    {
        $response = AddEmailSuppressionsResponse::fromArray([]);

        $this->assertSame([], $response->added);
        $this->assertSame([], $response->existing);
    }

    /**
     * @return array<string, mixed>
     */
    private function item(string $id, string $email): array
    {
        return [
            'id' => $id,
            'email' => $email,
            'reason' => 'MANUAL',
            'isAddedByClient' => true,
            'isRemovable' => true,
            'createdAt' => '2026-11-01T10:00:00+00:00',
        ];
    }
}
