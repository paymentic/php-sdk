<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Infrastructure\Http;

use Paymentic\Sdk\Shared\Enum\ApiErrorCode;
use Paymentic\Sdk\Shared\Exception\BadRequestException;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Paymentic\Sdk\Shared\Exception\UnauthorizedException;
use Paymentic\Sdk\Shared\Exception\ValidationException;
use Paymentic\Sdk\Shared\Http\ErrorResponseHandler;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ErrorResponseHandlerTest extends TestCase
{
    /**
     * @throws PaymenticException
     */
    #[Test]
    public function throwsBadRequestExceptionFor400(): void
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Bad request error');
        $this->expectExceptionCode(400);

        ErrorResponseHandler::handle(400, [
            'errors' => [
                [
                    'message' => 'Bad request error',
                ],
            ],
        ]);
    }

    /**
     * @throws PaymenticException
     */
    #[Test]
    public function throwsUnauthorizedExceptionFor401(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Invalid API key');
        $this->expectExceptionCode(401);

        ErrorResponseHandler::handle(401, [
            'errors' => [
                [
                    'message' => 'Invalid API key',
                ],
            ],
        ]);
    }

    /**
     * @throws PaymenticException
     */
    #[Test]
    public function throwsNotFoundExceptionFor404(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Resource not found');
        $this->expectExceptionCode(404);

        ErrorResponseHandler::handle(404, [
            'errors' => [['message' => 'Resource not found']],
        ]);
    }

    /**
     * @throws PaymenticException
     */
    #[Test]
    public function throwsValidationExceptionFor422(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validation failed');
        $this->expectExceptionCode(422);

        ErrorResponseHandler::handle(422, [
            'errors' => [
                [
                    'message' => 'Validation failed',
                    'field' => 'amount',
                ],
            ],
        ]);
    }

    #[Test]
    public function throwsPaymenticExceptionForOtherStatusCodes(): void
    {
        $this->expectException(PaymenticException::class);
        $this->expectExceptionMessage('Server error');
        $this->expectExceptionCode(500);

        ErrorResponseHandler::handle(500, [
            'errors' => [
                [
                    'message' => 'Server error',
                ],
            ],
        ]);
    }

    #[Test]
    public function parsesApiErrorsCorrectly(): void
    {
        $this->expectException(ValidationException::class);

        try {
            ErrorResponseHandler::handle(422, [
                'errors' => [
                    [
                        'message' => 'Amount is required',
                        'field' => 'amount',
                        'code' => 'VALIDATION_ERROR',
                    ],
                    [
                        'message' => 'Invalid currency',
                        'field' => 'currency',
                        'code' => 'VALIDATION_ERROR',
                    ],
                ],
            ]);
        } catch (ValidationException $e) {
            $errors = $e->getErrors();

            $this->assertCount(2, $errors);
            $this->assertSame('Amount is required', $errors[0]->message);
            $this->assertSame('amount', $errors[0]->field);
            $this->assertSame(ApiErrorCode::VALIDATION_ERROR, $errors[0]->code);
            $this->assertSame('Invalid currency', $errors[1]->message);
            $this->assertSame('currency', $errors[1]->field);

            throw $e;
        }
    }

    #[Test]
    public function handlesNullResponseBody(): void
    {
        $this->expectException(PaymenticException::class);
        $this->expectExceptionMessage('An error occurred');

        ErrorResponseHandler::handle(500, null);
    }

    #[Test]
    public function handlesEmptyErrorsArray(): void
    {
        $this->expectException(PaymenticException::class);
        $this->expectExceptionMessage('An error occurred');

        ErrorResponseHandler::handle(500, ['errors' => []]);
    }
}
