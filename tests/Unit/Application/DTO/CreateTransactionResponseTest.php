<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\DTO;

use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionResponse;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateTransactionResponseTest extends TestCase
{
    #[Test]
    public function createsFromArray(): void
    {
        $data = [
            'id' => 'TXN-123-456',
            'redirectUrl' => 'https://payment.example.com/pay/TXN-123-456',
        ];

        $response = CreateTransactionResponse::fromArray($data);

        $this->assertSame('TXN-123-456', $response->id);
        $this->assertSame('https://payment.example.com/pay/TXN-123-456', $response->redirectUrl);
        $this->assertNull($response->whitelabel);
    }

    #[Test]
    public function createsFromArrayWithWhitelabel(): void
    {
        $whitelabelData = [
            'token' => 'wl-token-123',
            'expiresAt' => '2024-12-31T23:59:59Z',
        ];

        $data = [
            'id' => 'TXN-789',
            'redirectUrl' => 'https://payment.example.com/pay/TXN-789',
            'whitelabel' => $whitelabelData,
        ];

        $response = CreateTransactionResponse::fromArray($data);

        $this->assertSame('TXN-789', $response->id);
        $this->assertSame($whitelabelData, $response->whitelabel);
    }

    #[Test]
    public function createsDirectly(): void
    {
        $response = new CreateTransactionResponse(
            id: 'TXN-DIRECT-001',
            redirectUrl: 'https://example.com/redirect',
            whitelabel: ['key' => 'value'],
        );

        $this->assertSame('TXN-DIRECT-001', $response->id);
        $this->assertSame('https://example.com/redirect', $response->redirectUrl);
        $this->assertSame(['key' => 'value'], $response->whitelabel);
    }
}
