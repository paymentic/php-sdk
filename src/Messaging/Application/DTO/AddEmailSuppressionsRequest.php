<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\DTO;

final readonly class AddEmailSuppressionsRequest
{
    /**
     * @param array<int, string> $emails
     */
    public function __construct(
        public array $emails,
    ) {
    }

    /**
     * @return array{emails: list<string>}
     */
    public function toArray(): array
    {
        return [
            'emails' => array_values($this->emails),
        ];
    }
}
