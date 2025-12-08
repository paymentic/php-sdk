<?php

declare(strict_types=1);

namespace Paymentic\Tests\Support;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class MockResponse implements ResponseInterface
{
    /**
     * @param array<string, mixed> $body
     */
    public function __construct(
        private readonly array $body,
        private readonly int $statusCode,
    ) {
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): StreamInterface
    {
        return new MockStream(json_encode($this->body, JSON_THROW_ON_ERROR));
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        return $this;
    }

    public function getReasonPhrase(): string
    {
        return '';
    }

    public function getProtocolVersion(): string
    {
        return '1.1';
    }

    public function withProtocolVersion(string $version): static
    {
        return $this;
    }

    public function getHeaders(): array
    {
        return [];
    }

    public function hasHeader(string $name): bool
    {
        return false;
    }

    public function getHeader(string $name): array
    {
        return [];
    }

    public function getHeaderLine(string $name): string
    {
        return '';
    }

    public function withHeader(string $name, $value): static
    {
        return $this;
    }

    public function withAddedHeader(string $name, $value): static
    {
        return $this;
    }

    public function withoutHeader(string $name): static
    {
        return $this;
    }

    public function withBody(StreamInterface $body): static
    {
        return $this;
    }
}
