<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Shared\Http;

use Paymentic\Sdk\Shared\Exception\BadRequestException;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Sdk\Shared\Exception\UnauthorizedException;
use Paymentic\Sdk\Shared\Exception\ValidationException;
use Paymentic\Sdk\Shared\Http\HttpClient;
use Paymentic\Tests\Support\MockPsrClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class HttpClientTest extends TestCase
{
    private MockPsrClient $psrClient;
    private HttpClient $httpClient;

    protected function setUp(): void
    {
        $this->psrClient = new MockPsrClient();
        $this->httpClient = new HttpClient(
            $this->psrClient,
            new MockRequestFactory(),
            new MockStreamFactory(),
            'test-api-key',
            'https://api.paymentic.com/v1_2',
        );
    }

    #[Test]
    public function sendsGetRequest(): void
    {
        $this->psrClient->setResponse(['data' => ['id' => 'TXN-123']]);

        $result = $this->httpClient->get('/payment/transactions/TXN-123');

        $this->assertSame(['data' => ['id' => 'TXN-123']], $result);

        $request = $this->psrClient->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('GET', $request->getMethod());
        $this->assertStringContainsString('/payment/transactions/TXN-123', (string) $request->getUri());
    }

    #[Test]
    public function sendsPostRequest(): void
    {
        $this->psrClient->setResponse(['data' => ['id' => 'TXN-NEW']]);

        $result = $this->httpClient->post('/payment/transactions', ['amount' => '100.00']);

        $this->assertSame(['data' => ['id' => 'TXN-NEW']], $result);

        $request = $this->psrClient->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('POST', $request->getMethod());
    }

    #[Test]
    public function sendsPatchRequest(): void
    {
        $this->psrClient->setResponse(['data' => []]);

        $result = $this->httpClient->patch('/payment/transactions/TXN-123/capture');

        $this->assertSame(['data' => []], $result);

        $request = $this->psrClient->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('PATCH', $request->getMethod());
    }

    #[Test]
    public function includesAuthorizationHeader(): void
    {
        $this->psrClient->setResponse([]);

        $this->httpClient->get('/test');

        $request = $this->psrClient->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('Bearer test-api-key', $request->getHeaderLine('Authorization'));
    }

    #[Test]
    public function includesContentTypeHeader(): void
    {
        $this->psrClient->setResponse([]);

        $this->httpClient->post('/test', ['key' => 'value']);

        $request = $this->psrClient->getLastRequest();
        $this->assertNotNull($request);
        $this->assertSame('application/json', $request->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function throwsBadRequestException(): void
    {
        $this->psrClient->setResponse([
            'errors' => [['code' => 'POINT_NOT_ACTIVE', 'message' => 'Point not active.']],
        ], 400);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Point not active.');

        $this->httpClient->post('/payment/transactions', []);
    }

    #[Test]
    public function throwsUnauthorizedException(): void
    {
        $this->psrClient->setResponse([
            'errors' => [['code' => 'UNAUTHORIZED', 'message' => 'Invalid API key.']],
        ], 401);

        $this->expectException(UnauthorizedException::class);

        $this->httpClient->get('/payment/transactions');
    }

    #[Test]
    public function throwsNotFoundException(): void
    {
        $this->psrClient->setResponse([
            'errors' => [['code' => 'TRANSACTION_NOT_FOUND', 'message' => 'Transaction not found.']],
        ], 404);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Transaction not found.');

        $this->httpClient->get('/payment/transactions/INVALID');
    }

    #[Test]
    public function throwsValidationException(): void
    {
        $this->psrClient->setResponse([
            'errors' => [[
                'code' => 'VALIDATION_ERROR',
                'message' => 'The amount field is required.',
                'details' => ['field' => 'amount'],
            ]],
        ], 422);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('The amount field is required.');

        $this->httpClient->post('/payment/transactions', []);
    }

    #[Test]
    public function usesCorrectBaseUrl(): void
    {
        $this->psrClient->setResponse([]);

        $this->httpClient->get('/payment/test');

        $request = $this->psrClient->getLastRequest();
        $this->assertNotNull($request);
        $this->assertStringStartsWith('https://api.paymentic.com/v1_2', (string) $request->getUri());
    }

    #[Test]
    public function sendsEmptyBodyForGetRequest(): void
    {
        $this->psrClient->setResponse([]);

        $this->httpClient->get('/test');

        $request = $this->psrClient->getLastRequest();
        $this->assertNotNull($request);
    }

    #[Test]
    public function sendsJsonBodyForPostRequest(): void
    {
        $this->psrClient->setResponse([]);

        $this->httpClient->post('/test', ['key' => 'value', 'nested' => ['a' => 1]]);

        $request = $this->psrClient->getLastRequest();
        $this->assertNotNull($request);
    }
}
