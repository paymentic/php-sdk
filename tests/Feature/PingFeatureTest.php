<?php

declare(strict_types=1);

namespace Paymentic\Tests\Feature;

use JsonException;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PingFeatureTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[Test]
    public function pingsSuccessfully(): void
    {
        $responseBody = json_encode([
            'data' => [
                'message' => 'pong',
                'environment' => 'sandbox',
                'tokenId' => '2a77157f-7a73-413d-90a1-cd1263533d61',
                'clientId' => '72b631fe',
                'version' => '1.2',
                'scopes' => ['*'],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 200);

        $response = $client->payment()->system()->ping();

        $this->assertSame('pong', $response->message);
        $this->assertSame('sandbox', $response->environment);
        $this->assertSame('2a77157f-7a73-413d-90a1-cd1263533d61', $response->tokenId);
        $this->assertSame('72b631fe', $response->clientId);
        $this->assertSame('1.2', $response->version);
        $this->assertSame(['*'], $response->scopes);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function pingsWithMultipleScopes(): void
    {
        $responseBody = json_encode([
            'data' => [
                'message' => 'pong',
                'environment' => 'production',
                'tokenId' => 'token-abc-123',
                'clientId' => 'client-xyz',
                'version' => '1.2',
                'scopes' => ['payments.read', 'payments.write', 'refunds.read'],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 200);

        $response = $client->payment()->system()->ping();

        $this->assertSame('production', $response->environment);
        $this->assertCount(3, $response->scopes);
        $this->assertSame('payments.read', $response->scopes[0]);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function pingVerifiesRequestMethod(): void
    {
        $responseBody = json_encode([
            'data' => [
                'message' => 'pong',
                'environment' => 'sandbox',
                'tokenId' => 'token-123',
                'clientId' => 'client-456',
                'version' => '1.2',
                'scopes' => ['*'],
            ],
        ], JSON_THROW_ON_ERROR);

        $mockHttpClient = new MockHttpClient($responseBody, 200);

        $client = PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient($mockHttpClient)
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();

        $client->payment()->system()->ping();

        $lastRequest = $mockHttpClient->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('GET', $lastRequest->getMethod());
        $this->assertStringContainsString('/payment/ping', (string) $lastRequest->getUri());
    }

    private function createClient(string $responseBody, int $statusCode): PaymenticClient
    {
        return PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient(new MockHttpClient($responseBody, $statusCode))
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();
    }
}
