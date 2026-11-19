<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Messaging\Application\DTO;

use Exception;
use Paymentic\Sdk\Messaging\Application\Mapper\EmailSuppressionMapper;
use Paymentic\Sdk\Messaging\Domain\Entity\EmailSuppression;

final readonly class AddEmailSuppressionsResponse
{
    /**
     * @param EmailSuppression[] $added
     * @param EmailSuppression[] $existing
     */
    public function __construct(
        public array $added,
        public array $existing,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @throws Exception
     */
    public static function fromArray(array $data): self
    {
        return new self(
            added: EmailSuppressionMapper::fromArrayCollection($data['added'] ?? []),
            existing: EmailSuppressionMapper::fromArrayCollection($data['existing'] ?? []),
        );
    }
}
