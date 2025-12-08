<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\ValueObject;

use Paymentic\Sdk\Shared\Enum\ApiErrorCode;

final readonly class ApiError
{
    public function __construct(
        public string $message,
        public ?string $field = null,
        public ?ApiErrorCode $code = null,
        public ?string $rawCode = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $rawCode = $data['code'] ?? null;

        return new self(
            message: $data['message'] ?? 'Unknown error',
            field: $data['field'] ?? null,
            code: ApiErrorCode::tryFromString($rawCode),
            rawCode: $rawCode,
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
