<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Laravel;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use JsonException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final readonly class LaravelPsr18Client implements ClientInterface
{
    public function __construct(
        private HttpFactory $http,
    ) {
    }

    /**
     * @throws ConnectionException
     * @throws JsonException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $method = strtolower($request->getMethod());
        $uri = (string) $request->getUri();

        /** @var array<string, string> $headers */
        $headers = array_map(static fn (array $values): string => implode(', ', $values), $request->getHeaders());

        $body = (string) $request->getBody();
        $data = $body !== '' ? json_decode($body, true, 512, JSON_THROW_ON_ERROR) : [];

        $pendingRequest = $this->http->withHeaders($headers);

        /** @var Response $response */
        $response = match ($method) {
            'get' => $pendingRequest->get($uri),
            'post' => $pendingRequest->post($uri, $data),
            'put' => $pendingRequest->put($uri, $data),
            'patch' => $pendingRequest->patch($uri, $data),
            'delete' => $pendingRequest->delete($uri, $data),
            default => $pendingRequest->send($method, $uri, ['body' => $body]),
        };

        return $response->toPsrResponse();
    }
}
