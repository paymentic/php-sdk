<?php

declare(strict_types=1);

namespace Paymentic\Tests\Support;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

final class MockHttpClient implements ClientInterface
{
    private string $responseBody;
    private int $statusCode;
    private ?RequestInterface $lastRequest = null;

    public function __construct(string $responseBody = '{}', int $statusCode = 200)
    {
        $this->responseBody = $responseBody;
        $this->statusCode = $statusCode;
    }

    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->lastRequest = $request;

        return new class ($this->responseBody, $this->statusCode) implements ResponseInterface {
            public function __construct(
                private readonly string $body,
                private readonly int $statusCode,
            ) {
            }

            public function getStatusCode(): int
            {
                return $this->statusCode;
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
                return ['Content-Type' => ['application/json']];
            }

            public function hasHeader(string $name): bool
            {
                return strtolower($name) === 'content-type';
            }

            public function getHeader(string $name): array
            {
                return strtolower($name) === 'content-type' ? ['application/json'] : [];
            }

            public function getHeaderLine(string $name): string
            {
                return strtolower($name) === 'content-type' ? 'application/json' : '';
            }

            public function withHeader(string $name, $value): MessageInterface
            {
                return $this;
            }

            public function withAddedHeader(string $name, $value): MessageInterface
            {
                return $this;
            }

            public function withoutHeader(string $name): MessageInterface
            {
                return $this;
            }

            public function getBody(): StreamInterface
            {
                return new class ($this->body) implements StreamInterface {
                    public function __construct(private readonly string $content)
                    {
                    }

                    public function __toString(): string
                    {
                        return $this->content;
                    }

                    public function close(): void
                    {
                    }

                    public function detach()
                    {
                        return null;
                    }

                    public function getSize(): ?int
                    {
                        return strlen($this->content);
                    }

                    public function tell(): int
                    {
                        return 0;
                    }

                    public function eof(): bool
                    {
                        return true;
                    }

                    public function isSeekable(): bool
                    {
                        return false;
                    }

                    public function seek(int $offset, int $whence = SEEK_SET): void
                    {
                    }

                    public function rewind(): void
                    {
                    }

                    public function isWritable(): bool
                    {
                        return false;
                    }

                    public function write(string $string): int
                    {
                        return 0;
                    }

                    public function isReadable(): bool
                    {
                        return true;
                    }

                    public function read(int $length): string
                    {
                        return $this->content;
                    }

                    public function getContents(): string
                    {
                        return $this->content;
                    }

                    public function getMetadata(?string $key = null): null
                    {
                        return null;
                    }
                };
            }

            public function withBody(StreamInterface $body): MessageInterface
            {
                return $this;
            }
        };
    }

    public function getLastRequest(): ?RequestInterface
    {
        return $this->lastRequest;
    }
}
