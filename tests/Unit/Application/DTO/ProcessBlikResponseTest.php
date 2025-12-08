<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\DTO;

use Paymentic\Sdk\Payment\Application\DTO\ProcessBlikResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ProcessBlikResponseTest extends TestCase
{
    #[Test]
    public function createsFromArray(): void
    {
        $data = [
            'actionId' => 'action-123-456',
        ];

        $response = ProcessBlikResponse::fromArray($data);

        $this->assertSame('action-123-456', $response->actionId);
        $this->assertNull($response->alias);
    }

    #[Test]
    public function createsFromArrayWithAlias(): void
    {
        $aliasData = [
            'value' => 'alias-value',
            'label' => 'My Bank',
        ];

        $data = [
            'actionId' => 'action-789',
            'alias' => $aliasData,
        ];

        $response = ProcessBlikResponse::fromArray($data);

        $this->assertSame('action-789', $response->actionId);
        $this->assertSame($aliasData, $response->alias);
    }

    #[Test]
    public function createsDirectly(): void
    {
        $response = new ProcessBlikResponse(
            actionId: 'direct-action-001',
            alias: 'simple-alias',
        );

        $this->assertSame('direct-action-001', $response->actionId);
        $this->assertSame('simple-alias', $response->alias);
    }
}
