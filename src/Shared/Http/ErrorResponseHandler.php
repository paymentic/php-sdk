<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Shared\Http;

use Paymentic\Sdk\Shared\Exception\BadRequestException;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Paymentic\Sdk\Shared\Exception\UnauthorizedException;
use Paymentic\Sdk\Shared\Exception\ValidationException;
use Paymentic\Sdk\Shared\ValueObject\ApiError;

final class ErrorResponseHandler
{
    /**
     * @param array<string, mixed>|null $responseBody
     * @throws PaymenticException
     */
    public static function handle(int $statusCode, ?array $responseBody): never
    {
        $rawErrors = $responseBody['errors'] ?? [];
        $errors = ApiError::fromArrayList($rawErrors);
        $message = isset($errors[0]) ? $errors[0]->message : 'An error occurred';

        throw match ($statusCode) {
            400 => new BadRequestException($message, $errors, $statusCode),
            401 => new UnauthorizedException($message, $errors, $statusCode),
            404 => new NotFoundException($message, $errors, $statusCode),
            422 => new ValidationException($message, $errors, $statusCode),
            default => new PaymenticException($message, $errors, $statusCode),
        };
    }
}
