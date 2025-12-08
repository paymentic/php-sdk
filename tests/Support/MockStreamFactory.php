<?php

declare(strict_types=1);

namespace Paymentic\Tests\Support;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

final class MockStreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        return new class ($content) implements StreamInterface {
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

            public function detach(): null
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

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        return $this->createStream(file_get_contents($filename) ?: '');
    }

    public function createStreamFromResource($resource): StreamInterface
    {
        return $this->createStream('');
    }
}
