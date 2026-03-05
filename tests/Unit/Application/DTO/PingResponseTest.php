<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\DTO;

use Paymentic\Sdk\Payment\Application\DTO\PingResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PingResponseTest extends TestCase
{
    #[Test]
    public function createsFromArray(): void
    {
        $data = [
            'message' => 'pong',
            'environment' => 'sandbox',
            'tokenId' => '2a77157f-7a73-413d-90a1-cd1263533d61',
            'clientId' => '72b631fe',
            'version' => '1.2',
            'scopes' => ['*'],
        ];

        $response = PingResponse::fromArray($data);

        $this->assertSame('pong', $response->message);
        $this->assertSame('sandbox', $response->environment);
        $this->assertSame('2a77157f-7a73-413d-90a1-cd1263533d61', $response->tokenId);
        $this->assertSame('72b631fe', $response->clientId);
        $this->assertSame('1.2', $response->version);
        $this->assertSame(['*'], $response->scopes);
    }

    #[Test]
    public function createsFromArrayWithMultipleScopes(): void
    {
        $data = [
            'message' => 'pong',
            'environment' => 'production',
            'tokenId' => 'token-123',
            'clientId' => 'client-456',
            'version' => '1.2',
            'scopes' => ['payments.read', 'payments.write'],
        ];

        $response = PingResponse::fromArray($data);

        $this->assertSame('production', $response->environment);
        $this->assertCount(2, $response->scopes);
        $this->assertSame('payments.read', $response->scopes[0]);
        $this->assertSame('payments.write', $response->scopes[1]);
    }

    #[Test]
    public function createsFromArrayWithEmptyScopes(): void
    {
        $data = [
            'message' => 'pong',
            'environment' => 'sandbox',
            'tokenId' => 'token-123',
            'clientId' => 'client-456',
            'version' => '1.2',
        ];

        $response = PingResponse::fromArray($data);

        $this->assertSame([], $response->scopes);
    }
}
