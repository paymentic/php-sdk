<?php

declare(strict_types=1);

namespace Paymentic\Tests\Support;

use Paymentic\Sdk\Shared\Http\HttpClient;

final class MockHttpClientFactory
{
    /**
     * @param array<string, mixed> $response
     */
    public static function create(array $response = [], int $statusCode = 200): HttpClient
    {
        $psrClient = new MockPsrClient($response, $statusCode);

        return new HttpClient(
            $psrClient,
            new MockRequestFactory(),
            new MockStreamFactory(),
            'test-api-key',
            'https://api.test.paymentic.com',
        );
    }
}
