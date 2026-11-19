<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Http;

use JsonException;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class HttpClient
{
    public function __construct(
        private ClientInterface $client,
        private RequestFactoryInterface $requestFactory,
        private StreamFactoryInterface $streamFactory,
        private string $apiKey,
        private string $baseUrl,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws JsonException
     * @throws PaymenticException
     */
    public function post(string $uri, array $data = [], array $headers = []): array
    {
        return $this->request('POST', $uri, $data, $headers);
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws JsonException
     * @throws PaymenticException
     */
    public function get(string $uri, array $headers = []): array
    {
        return $this->request('GET', $uri, [], $headers);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws JsonException
     * @throws PaymenticException
     */
    public function patch(string $uri, array $data = [], array $headers = []): array
    {
        return $this->request('PATCH', $uri, $data, $headers);
    }

    /**
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws JsonException
     * @throws PaymenticException
     */
    public function delete(string $uri, array $headers = []): array
    {
        return $this->request('DELETE', $uri, [], $headers);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, string> $headers
     * @return array<string, mixed>
     * @throws JsonException
     * @throws PaymenticException
     */
    private function request(string $method, string $uri, array $data = [], array $headers = []): array
    {
        $request = $this->requestFactory->createRequest($method, $this->baseUrl . $uri)
            ->withHeader('Authorization', 'Bearer ' . $this->apiKey)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Accept', 'application/json');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if ([] !== $data) {
            $body = $this->streamFactory->createStream(json_encode($data, JSON_THROW_ON_ERROR));
            $request = $request->withBody($body);
        }

        $response = $this->client->sendRequest($request);
        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();
        $decoded = '' === trim($body) ? null : json_decode($body, true, 512, JSON_THROW_ON_ERROR);

        if (200 <= $statusCode && 300 > $statusCode) {
            return $decoded ?? [];
        }

        ErrorResponseHandler::handle($statusCode, $decoded);
    }
}
