<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Exception;

use Exception;
use Paymentic\Sdk\Shared\ValueObject\ApiError;
use Throwable;

class PaymenticException extends Exception
{
    /**
     * @param array<ApiError> $errors
     */
    public function __construct(
        string $message,
        private readonly array $errors = [],
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<ApiError>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?ApiError
    {
        return $this->errors[0] ?? null;
    }

    public function hasFieldError(string $field): bool
    {
        foreach ($this->errors as $error) {
            if ($error->field === $field) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<ApiError>
     */
    public function getFieldErrors(string $field): array
    {
        return array_filter(
            $this->errors,
            static fn (ApiError $error) => $error->field === $field
        );
    }

    /**
     * @return array<string, array<ApiError>>
     */
    public function getErrorsByField(): array
    {
        $grouped = [];
        foreach ($this->errors as $error) {
            $key = $error->field ?? '_general';
            $grouped[$key][] = $error;
        }

        return $grouped;
    }
}
