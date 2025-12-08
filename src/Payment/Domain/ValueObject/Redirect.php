<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Domain\ValueObject;

use Paymentic\Sdk\Shared\Exception\InvalidValueException;

final readonly class Redirect
{
    public function __construct(
        public ?string $success = null,
        public ?string $failure = null,
    ) {
        if (null !== $success && false === filter_var($success, FILTER_VALIDATE_URL)) {
            throw InvalidValueException::invalidUrl($success, 'success');
        }

        if (null !== $failure && false === filter_var($failure, FILTER_VALIDATE_URL)) {
            throw InvalidValueException::invalidUrl($failure, 'failure');
        }
    }

    /**
     * @return array<string, string|null>
     */
    public function toArray(): array
    {
        return array_filter([
            'success' => $this->success,
            'failure' => $this->failure,
        ], static fn ($value) => null !== $value);
    }
}
