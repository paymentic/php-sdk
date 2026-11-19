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
use Psr\Http\Message\RequestInterface;

final class MessagingPingFeatureTest extends TestCase
{
    private ?MockHttpClient $http = null;

    /**
     * @throws JsonException
     */
    #[Test]
    public function pingsMessagingApi(): void
    {
        $client = $this->createClient(json_encode([
            'data' => [
                'message' => 'pong',
                'environment' => 'sandbox',
                'tokenId' => '2a77157f-7a73-413d-90a1-cd1263533d61',
                'clientId' => '72b631fe',
                'version' => '1.2',
                'scopes' => ['messaging.sms.send', 'messaging.email.send'],
            ],
        ], JSON_THROW_ON_ERROR), 200);

        $response = $client->messaging()->system()->ping();

        $this->assertSame('GET', $this->lastRequest()->getMethod());
        $this->assertStringEndsWith('/v1_2/messaging/ping', (string) $this->lastRequest()->getUri());
        $this->assertSame('pong', $response->message);
        $this->assertSame('sandbox', $response->environment);
        $this->assertSame('2a77157f-7a73-413d-90a1-cd1263533d61', $response->tokenId);
        $this->assertSame('72b631fe', $response->clientId);
        $this->assertSame('1.2', $response->version);
        $this->assertSame(['messaging.sms.send', 'messaging.email.send'], $response->scopes);
    }

    private function createClient(string $responseBody, int $statusCode): PaymenticClient
    {
        $this->http = new MockHttpClient($responseBody, $statusCode);

        return PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient($this->http)
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();
    }

    private function lastRequest(): RequestInterface
    {
        $request = $this->http?->getLastRequest();
        $this->assertNotNull($request);

        return $request;
    }
}
