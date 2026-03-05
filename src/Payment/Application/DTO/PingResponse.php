<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\DTO;

final readonly class PingResponse
{
    /**
     * @param string[] $scopes
     */
    public function __construct(
        public string $message,
        public string $environment,
        public string $tokenId,
        public string $clientId,
        public string $version,
        public array $scopes = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            message: $data['message'],
            environment: $data['environment'],
            tokenId: $data['tokenId'],
            clientId: $data['clientId'],
            version: $data['version'],
            scopes: $data['scopes'] ?? [],
        );
    }
}
