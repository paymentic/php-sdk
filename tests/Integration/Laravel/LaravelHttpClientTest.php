<?php

declare(strict_types=1);

namespace Paymentic\Tests\Integration\Laravel;

use GuzzleHttp\Psr7\Request;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as LaravelRequest;
use Illuminate\Support\Facades\Http;
use JsonException;
use Orchestra\Testbench\TestCase;
use Paymentic\Sdk\Integration\Laravel\LaravelPsr18Client;
use PHPUnit\Framework\Attributes\Test;

final class LaravelHttpClientTest extends TestCase
{
    private LaravelPsr18Client $client;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new LaravelPsr18Client(
            http: $this->app->make(HttpFactory::class),
        );
    }

    /**
     * @throws ConnectionException
     * @throws JsonException
     */
    #[Test]
    public function sendsGetRequest(): void
    {
        Http::fake([
            '*' => Http::response(['data' => ['id' => 'TXN-123']], 200),
        ]);

        $request = new Request('GET', 'https://api.paymentic.com/v1_2/test', [
            'Authorization' => 'Bearer test-key',
        ]);

        $response = $this->client->sendRequest($request);

        $this->assertSame(200, $response->getStatusCode());

        Http::assertSent(static fn (LaravelRequest $r): bool => $r->method() === 'GET');
    }

    /**
     * @throws ConnectionException
     * @throws JsonException
     */
    #[Test]
    public function sendsPostRequest(): void
    {
        Http::fake([
            '*' => Http::response(['data' => ['id' => 'TXN-123']], 201),
        ]);

        $request = new Request(
            'POST',
            'https://api.paymentic.com/v1_2/test',
            ['Content-Type' => 'application/json'],
            json_encode(['amount' => '100.00'], JSON_THROW_ON_ERROR),
        );

        $response = $this->client->sendRequest($request);

        $this->assertSame(201, $response->getStatusCode());

        Http::assertSent(static fn (LaravelRequest $r): bool => $r->method() === 'POST');
    }

    /**
     * @throws ConnectionException
     * @throws JsonException
     */
    #[Test]
    public function sendsPatchRequest(): void
    {
        Http::fake([
            '*' => Http::response(['data' => []], 200),
        ]);

        $request = new Request('PATCH', 'https://api.paymentic.com/v1_2/test');

        $response = $this->client->sendRequest($request);

        $this->assertSame(200, $response->getStatusCode());

        Http::assertSent(static fn (LaravelRequest $r): bool => $r->method() === 'PATCH');
    }

    /**
     * @throws JsonException
     * @throws ConnectionException
     */
    #[Test]
    public function forwardsHeaders(): void
    {
        Http::fake(['*' => Http::response([], 200)]);

        $request = new Request('GET', 'https://api.paymentic.com/test', [
            'Authorization' => 'Bearer my-api-key',
            'X-Custom-Header' => 'custom-value',
        ]);

        $this->client->sendRequest($request);

        Http::assertSent(static function (LaravelRequest $r): bool {
            return $r->hasHeader('Authorization', 'Bearer my-api-key')
                && $r->hasHeader('X-Custom-Header', 'custom-value');
        });
    }

    /**
     * @throws ConnectionException
     * @throws JsonException
     */
    #[Test]
    public function returnsPsrResponse(): void
    {
        Http::fake([
            '*' => Http::response(['success' => true], 200),
        ]);

        $request = new Request('GET', 'https://api.paymentic.com/test');

        $response = $this->client->sendRequest($request);

        $body = json_decode((string)$response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($body['success']);
    }
}
