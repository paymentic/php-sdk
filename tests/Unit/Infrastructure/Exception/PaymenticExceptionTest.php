<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Infrastructure\Exception;

use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Paymentic\Sdk\Shared\ValueObject\ApiError;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PaymenticExceptionTest extends TestCase
{
    #[Test]
    public function createsWithMessageAndErrors(): void
    {
        $errors = [
            new ApiError('Error 1', 'field1'),
            new ApiError('Error 2', 'field2'),
        ];

        $exception = new PaymenticException('Main error', $errors, 422);

        $this->assertSame('Main error', $exception->getMessage());
        $this->assertSame(422, $exception->getCode());
        $this->assertCount(2, $exception->getErrors());
    }

    #[Test]
    public function getsFirstError(): void
    {
        $errors = [
            new ApiError('First error', 'field1'),
            new ApiError('Second error', 'field2'),
        ];

        $exception = new PaymenticException('Error', $errors);

        $firstError = $exception->getFirstError();

        $this->assertNotNull($firstError);
        $this->assertSame('First error', $firstError->message);
        $this->assertSame('field1', $firstError->field);
    }

    #[Test]
    public function returnsNullForFirstErrorWhenEmpty(): void
    {
        $exception = new PaymenticException('Error', []);

        $this->assertNull($exception->getFirstError());
    }

    #[Test]
    public function checksIfHasFieldError(): void
    {
        $errors = [
            new ApiError('Error', 'email'),
            new ApiError('Error', 'amount'),
        ];

        $exception = new PaymenticException('Error', $errors);

        $this->assertTrue($exception->hasFieldError('email'));
        $this->assertTrue($exception->hasFieldError('amount'));
        $this->assertFalse($exception->hasFieldError('name'));
    }

    #[Test]
    public function getsFieldErrors(): void
    {
        $errors = [
            new ApiError('Required', 'email'),
            new ApiError('Invalid format', 'email'),
            new ApiError('Too short', 'name'),
        ];

        $exception = new PaymenticException('Error', $errors);

        $emailErrors = $exception->getFieldErrors('email');

        $this->assertCount(2, $emailErrors);
    }

    #[Test]
    public function getsErrorsByField(): void
    {
        $errors = [
            new ApiError('Required', 'email'),
            new ApiError('Invalid format', 'email'),
            new ApiError('Too short', 'name'),
            new ApiError('General error'),
        ];

        $exception = new PaymenticException('Error', $errors);

        $grouped = $exception->getErrorsByField();

        $this->assertArrayHasKey('email', $grouped);
        $this->assertArrayHasKey('name', $grouped);
        $this->assertArrayHasKey('_general', $grouped);
        $this->assertCount(2, $grouped['email']);
        $this->assertCount(1, $grouped['name']);
        $this->assertCount(1, $grouped['_general']);
    }
}
