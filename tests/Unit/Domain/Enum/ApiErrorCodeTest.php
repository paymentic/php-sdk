<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\Enum;

use Paymentic\Sdk\Shared\Enum\ApiErrorCode;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ApiErrorCodeTest extends TestCase
{
    #[Test]
    public function hasValidationErrorCase(): void
    {
        $this->assertSame('VALIDATION_ERROR', ApiErrorCode::VALIDATION_ERROR->value);
    }

    #[Test]
    public function hasTransactionNotFoundCase(): void
    {
        $this->assertSame('TRANSACTION_NOT_FOUND', ApiErrorCode::TRANSACTION_NOT_FOUND->value);
    }

    #[Test]
    public function hasRefundAmountTooHighCase(): void
    {
        $this->assertSame('REFUND_AMOUNT_TOO_HIGH', ApiErrorCode::REFUND_AMOUNT_TOO_HIGH->value);
    }

    #[Test]
    public function tryFromStringReturnsEnumForKnownCode(): void
    {
        $code = ApiErrorCode::tryFromString('VALIDATION_ERROR');

        $this->assertSame(ApiErrorCode::VALIDATION_ERROR, $code);
    }

    #[Test]
    public function tryFromStringReturnsNullForUnknownCode(): void
    {
        $code = ApiErrorCode::tryFromString('UNKNOWN_CODE');

        $this->assertNull($code);
    }

    #[Test]
    public function tryFromStringReturnsNullForNull(): void
    {
        $code = ApiErrorCode::tryFromString(null);

        $this->assertNull($code);
    }
}
