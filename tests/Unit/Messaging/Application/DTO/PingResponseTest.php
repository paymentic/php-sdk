<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Messaging\Application\DTO;

use Paymentic\Sdk\Messaging\Application\DTO\PingResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PingResponseTest extends TestCase
{
    #[Test]
    public function createsFromArray(): void
    {
        $response = PingResponse::fromArray([
            'message' => 'pong',
            'environment' => 'sandbox',
            'tokenId' => 'token-1',
            'clientId' => 'client-1',
            'version' => '1.2',
            'scopes' => ['messaging.sms.send', 'messaging.email.send'],
        ]);

        $this->assertSame('pong', $response->message);
        $this->assertSame('sandbox', $response->environment);
        $this->assertSame('token-1', $response->tokenId);
        $this->assertSame('client-1', $response->clientId);
        $this->assertSame('1.2', $response->version);
        $this->assertSame(['messaging.sms.send', 'messaging.email.send'], $response->scopes);
    }

    #[Test]
    public function defaultsScopesToEmptyArray(): void
    {
        $response = PingResponse::fromArray([
            'message' => 'pong',
            'environment' => 'sandbox',
            'tokenId' => 't',
            'clientId' => 'c',
            'version' => '1.2',
        ]);

        $this->assertSame([], $response->scopes);
    }
}
