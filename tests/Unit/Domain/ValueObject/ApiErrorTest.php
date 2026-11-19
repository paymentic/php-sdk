<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Shared\Enum\ApiErrorCode;
use Paymentic\Sdk\Shared\ValueObject\ApiError;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ApiErrorTest extends TestCase
{
    #[Test]
    public function createsWithAllFields(): void
    {
        $error = new ApiError(
            message: 'Field is required',
            field: 'email',
            code: ApiErrorCode::VALIDATION_ERROR,
            rawCode: 'VALIDATION_ERROR',
        );

        $this->assertSame('Field is required', $error->message);
        $this->assertSame('email', $error->field);
        $this->assertSame(ApiErrorCode::VALIDATION_ERROR, $error->code);
        $this->assertSame('VALIDATION_ERROR', $error->rawCode);
    }

    #[Test]
    public function ignoresNonStringFieldValues(): void
    {
        $error = ApiError::fromArray([
            'message' => 'Bad',
            'field' => ['x'],
            'details' => ['field' => 123],
        ]);
        $this->assertNull($error->field);

        $fromDetails = ApiError::fromArray(['message' => 'Bad', 'details' => ['field' => 'email']]);
        $this->assertSame('email', $fromDetails->field);

        $topLevelWins = ApiError::fromArray(['message' => 'Bad', 'field' => 'phone', 'details' => ['field' => 'email']]);
        $this->assertSame('phone', $topLevelWins->field);
    }

    #[Test]
    public function createsWithOnlyMessage(): void
    {
        $error = new ApiError(message: 'Something went wrong');

        $this->assertSame('Something went wrong', $error->message);
        $this->assertNull($error->field);
        $this->assertNull($error->code);
        $this->assertNull($error->rawCode);
    }

    #[Test]
    public function createsFromArrayWithKnownCode(): void
    {
        $data = [
            'message' => 'Invalid value',
            'field' => 'amount',
            'code' => 'VALIDATION_ERROR',
        ];

        $error = ApiError::fromArray($data);

        $this->assertSame('Invalid value', $error->message);
        $this->assertSame('amount', $error->field);
        $this->assertSame(ApiErrorCode::VALIDATION_ERROR, $error->code);
        $this->assertSame('VALIDATION_ERROR', $error->rawCode);
    }

    #[Test]
    public function createsFromArrayWithUnknownCode(): void
    {
        $data = [
            'message' => 'Some error',
            'code' => 'UNKNOWN_CODE',
        ];

        $error = ApiError::fromArray($data);

        $this->assertNull($error->code);
        $this->assertSame('UNKNOWN_CODE', $error->rawCode);
    }

    #[Test]
    public function createsFromArrayWithMissingFields(): void
    {
        $error = ApiError::fromArray(['message' => 'Error']);

        $this->assertSame('Error', $error->message);
        $this->assertNull($error->field);
        $this->assertNull($error->code);
    }

    #[Test]
    public function createsFromEmptyArrayWithDefaultMessage(): void
    {
        $error = ApiError::fromArray([]);

        $this->assertSame('Unknown error', $error->message);
    }

    #[Test]
    public function createsFromArrayList(): void
    {
        $data = [
            ['message' => 'Error 1', 'field' => 'field1'],
            ['message' => 'Error 2', 'field' => 'field2'],
        ];

        $errors = ApiError::fromArrayList($data);

        $this->assertCount(2, $errors);
        $this->assertSame('Error 1', $errors[0]->message);
        $this->assertSame('field1', $errors[0]->field);
        $this->assertSame('Error 2', $errors[1]->message);
        $this->assertSame('field2', $errors[1]->field);
    }

    #[Test]
    public function createsEmptyArrayFromEmptyList(): void
    {
        $errors = ApiError::fromArrayList([]);

        $this->assertSame([], $errors);
    }

    #[Test]
    public function parsesDetails(): void
    {
        $error = ApiError::fromArray([
            'message' => 'Recipient suppressed',
            'code' => 'EMAIL_RECIPIENT_SUPPRESSED',
            'details' => ['recipients' => ['a@b.pl']],
        ]);

        $this->assertSame(['recipients' => ['a@b.pl']], $error->details);
        $this->assertNull($error->field);
        $this->assertSame(ApiErrorCode::EMAIL_RECIPIENT_SUPPRESSED, $error->code);
    }

    #[Test]
    public function fieldFallsBackToDetailsField(): void
    {
        $error = ApiError::fromArray([
            'message' => 'Invalid',
            'details' => ['field' => 'recipient'],
        ]);

        $this->assertSame('recipient', $error->field);
    }

    #[Test]
    public function topLevelFieldWinsOverDetailsField(): void
    {
        $error = ApiError::fromArray([
            'message' => 'Invalid',
            'field' => 'top',
            'details' => ['field' => 'nested'],
        ]);

        $this->assertSame('top', $error->field);
    }

    #[Test]
    public function detailsAreNullWhenMissing(): void
    {
        $this->assertNull(ApiError::fromArray(['message' => 'Error'])->details);
    }
}
