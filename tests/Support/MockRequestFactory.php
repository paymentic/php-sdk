<?php

declare(strict_types=1);

namespace Paymentic\Tests\Support;

use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UriInterface;

final class MockRequestFactory implements RequestFactoryInterface
{
    public function createRequest(string $method, $uri): RequestInterface
    {
        return new class ($method, $uri) implements RequestInterface {
            private array $headers = [];
            private ?StreamInterface $body = null;

            public function __construct(
                private readonly string $method,
                private readonly string $uri,
            ) {
            }

            public function getRequestTarget(): string
            {
                return $this->uri;
            }

            public function withRequestTarget(string $requestTarget): RequestInterface
            {
                return $this;
            }

            public function getMethod(): string
            {
                return $this->method;
            }

            public function withMethod(string $method): RequestInterface
            {
                return $this;
            }

            public function getUri(): UriInterface
            {
                return new class ($this->uri) implements UriInterface {
                    public function __construct(private readonly string $uri)
                    {
                    }

                    public function getScheme(): string
                    {
                        return parse_url($this->uri, PHP_URL_SCHEME) ?? '';
                    }

                    public function getAuthority(): string
                    {
                        return parse_url($this->uri, PHP_URL_HOST) ?? '';
                    }

                    public function getUserInfo(): string
                    {
                        return '';
                    }

                    public function getHost(): string
                    {
                        return parse_url($this->uri, PHP_URL_HOST) ?? '';
                    }

                    public function getPort(): ?int
                    {
                        return parse_url($this->uri, PHP_URL_PORT);
                    }

                    public function getPath(): string
                    {
                        return parse_url($this->uri, PHP_URL_PATH) ?? '';
                    }

                    public function getQuery(): string
                    {
                        return parse_url($this->uri, PHP_URL_QUERY) ?? '';
                    }

                    public function getFragment(): string
                    {
                        return '';
                    }

                    public function withScheme(string $scheme): UriInterface
                    {
                        return $this;
                    }

                    public function withUserInfo(string $user, ?string $password = null): UriInterface
                    {
                        return $this;
                    }

                    public function withHost(string $host): UriInterface
                    {
                        return $this;
                    }

                    public function withPort(?int $port): UriInterface
                    {
                        return $this;
                    }

                    public function withPath(string $path): UriInterface
                    {
                        return $this;
                    }

                    public function withQuery(string $query): UriInterface
                    {
                        return $this;
                    }

                    public function withFragment(string $fragment): UriInterface
                    {
                        return $this;
                    }

                    public function __toString(): string
                    {
                        return $this->uri;
                    }
                };
            }

            public function withUri(UriInterface $uri, bool $preserveHost = false): RequestInterface
            {
                return $this;
            }

            public function getProtocolVersion(): string
            {
                return '1.1';
            }

            public function withProtocolVersion(string $version): MessageInterface
            {
                return $this;
            }

            public function getHeaders(): array
            {
                return $this->headers;
            }

            public function hasHeader(string $name): bool
            {
                return isset($this->headers[strtolower($name)]);
            }

            public function getHeader(string $name): array
            {
                return $this->headers[strtolower($name)] ?? [];
            }

            public function getHeaderLine(string $name): string
            {
                return implode(', ', $this->getHeader($name));
            }

            public function withHeader(string $name, $value): MessageInterface
            {
                $clone = clone $this;
                $clone->headers[strtolower($name)] = is_array($value) ? $value : [$value];

                return $clone;
            }

            public function withAddedHeader(string $name, $value): MessageInterface
            {
                return $this->withHeader($name, $value);
            }

            public function withoutHeader(string $name): MessageInterface
            {
                $clone = clone $this;
                unset($clone->headers[strtolower($name)]);

                return $clone;
            }

            public function getBody(): StreamInterface
            {
                return $this->body ?? new class () implements StreamInterface {
                    public function __toString(): string
                    {
                        return '';
                    }

                    public function close(): void
                    {
                    }

                    public function detach(): null
                    {
                        return null;
                    }

                    public function getSize(): ?int
                    {
                        return 0;
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
                        return '';
                    }

                    public function getContents(): string
                    {
                        return '';
                    }

                    public function getMetadata(?string $key = null)
                    {
                        return null;
                    }
                };
            }

            public function withBody(StreamInterface $body): MessageInterface
            {
                $clone = clone $this;
                $clone->body = $body;

                return $clone;
            }
        };
    }
}
