<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Infrastructure\Http;

use Paymentic\Sdk\Shared\Enum\ApiErrorCode;
use Paymentic\Sdk\Shared\Exception\BadRequestException;
use Paymentic\Sdk\Shared\Exception\ConflictException;
use Paymentic\Sdk\Shared\Exception\ForbiddenException;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Sdk\Shared\Exception\PayloadTooLargeException;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Paymentic\Sdk\Shared\Exception\PaymentRequiredException;
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

    /**
     * @throws PaymenticException
     */
    #[Test]
    public function throwsPaymentRequiredExceptionFor402(): void
    {
        $this->expectException(PaymentRequiredException::class);
        $this->expectExceptionMessage('Error 402');
        $this->expectExceptionCode(402);

        ErrorResponseHandler::handle(402, [
            'errors' => [['message' => 'Error 402']],
        ]);
    }

    /**
     * @throws PaymenticException
     */
    #[Test]
    public function throwsForbiddenExceptionFor403(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Error 403');
        $this->expectExceptionCode(403);

        ErrorResponseHandler::handle(403, [
            'errors' => [['message' => 'Error 403']],
        ]);
    }

    /**
     * @throws PaymenticException
     */
    #[Test]
    public function throwsConflictExceptionFor409(): void
    {
        $this->expectException(ConflictException::class);
        $this->expectExceptionMessage('Error 409');
        $this->expectExceptionCode(409);

        ErrorResponseHandler::handle(409, [
            'errors' => [['message' => 'Error 409']],
        ]);
    }

    /**
     * @throws PaymenticException
     */
    #[Test]
    public function throwsPayloadTooLargeExceptionFor413(): void
    {
        $this->expectException(PayloadTooLargeException::class);
        $this->expectExceptionMessage('Error 413');
        $this->expectExceptionCode(413);

        ErrorResponseHandler::handle(413, [
            'errors' => [['message' => 'Error 413']],
        ]);
    }

    #[Test]
    public function keepsGenericPaymenticExceptionFor405(): void
    {
        $this->expectException(PaymenticException::class);

        try {
            ErrorResponseHandler::handle(405, [
                'errors' => [['message' => 'Error 405']],
            ]);
        } catch (PaymenticException $e) {
            $this->assertSame(PaymenticException::class, $e::class);
            $this->assertSame(405, $e->getCode());

            throw $e;
        }
    }

    #[Test]
    public function keepsGenericPaymenticExceptionFor415(): void
    {
        $this->expectException(PaymenticException::class);

        try {
            ErrorResponseHandler::handle(415, [
                'errors' => [['message' => 'Error 415']],
            ]);
        } catch (PaymenticException $e) {
            $this->assertSame(PaymenticException::class, $e::class);
            $this->assertSame(415, $e->getCode());

            throw $e;
        }
    }
}
