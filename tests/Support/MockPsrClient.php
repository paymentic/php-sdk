<?php

declare(strict_types=1);

namespace Paymentic\Tests\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class MockPsrClient implements ClientInterface
{
    /** @var array<string, mixed> */
    private array $response;
    private int $statusCode;
    private ?RequestInterface $lastRequest = null;

    /**
     * @param array<string, mixed> $response
     */
    public function __construct(array $response = [], int $statusCode = 200)
    {
        $this->response = $response;
        $this->statusCode = $statusCode;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;

        return new MockResponse($this->response, $this->statusCode);
    }

    public function getLastRequest(): ?RequestInterface
    {
        return $this->lastRequest;
    }

    /**
     * @param array<string, mixed> $response
     */
    public function setResponse(array $response, int $statusCode = 200): void
    {
        $this->response = $response;
        $this->statusCode = $statusCode;
    }
}
