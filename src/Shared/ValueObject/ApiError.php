<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\ValueObject;

use Paymentic\Sdk\Shared\Enum\ApiErrorCode;

final readonly class ApiError
{
    /**
     * @param array<string, mixed>|null $details
     */
    public function __construct(
        public string $message,
        public ?string $field = null,
        public ?ApiErrorCode $code = null,
        public ?string $rawCode = null,
        public ?array $details = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $rawCode = $data['code'] ?? null;
        $details = is_array($data['details'] ?? null) ? $data['details'] : null;

        $field = $data['field'] ?? null;
        if (! is_string($field)) {
            $field = is_string($details['field'] ?? null) ? $details['field'] : null;
        }

        return new self(
            message: $data['message'] ?? 'Unknown error',
            field: $field,
            code: ApiErrorCode::tryFromString($rawCode),
            rawCode: $rawCode,
            details: $details,
        );
    }

    /**
     * @param array<int, array<string, mixed>> $errors
     * @return array<ApiError>
     */
    public static function fromArrayList(array $errors): array
    {
        return array_map(
            static fn (array $error): ApiError => self::fromArray($error),
            $errors
        );
    }
}
