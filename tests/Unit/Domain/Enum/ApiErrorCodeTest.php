<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\Enum;

use Paymentic\Sdk\Shared\Enum\ApiErrorCode;
use PHPUnit\Framework\Attributes\DataProvider;
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

    #[Test]
    #[DataProvider('messagingCodes')]
    public function tryFromStringResolvesMessagingCodes(string $code): void
    {
        $this->assertSame($code, ApiErrorCode::tryFromString($code)?->value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function messagingCodes(): iterable
    {
        yield 'FORBIDDEN' => ['FORBIDDEN'];
        yield 'INSUFFICIENT_FUNDS' => ['INSUFFICIENT_FUNDS'];
        yield 'WALLET_NOT_AVAILABLE' => ['WALLET_NOT_AVAILABLE'];
        yield 'PRICE_NOT_CONFIGURED' => ['PRICE_NOT_CONFIGURED'];
        yield 'SMS_SERVICE_NOT_ACTIVE' => ['SMS_SERVICE_NOT_ACTIVE'];
        yield 'MESSAGE_NOT_FOUND' => ['MESSAGE_NOT_FOUND'];
        yield 'PAYLOAD_TOO_LARGE' => ['PAYLOAD_TOO_LARGE'];
        yield 'UNSUPPORTED_MEDIA_TYPE' => ['UNSUPPORTED_MEDIA_TYPE'];
        yield 'IDEMPOTENCY_CONFLICT' => ['IDEMPOTENCY_CONFLICT'];
        yield 'IDEMPOTENCY_IN_PROGRESS' => ['IDEMPOTENCY_IN_PROGRESS'];
        yield 'EMAIL_SERVICE_NOT_ACTIVE' => ['EMAIL_SERVICE_NOT_ACTIVE'];
        yield 'EMAIL_SERVICE_PROVISIONING' => ['EMAIL_SERVICE_PROVISIONING'];
        yield 'EMAIL_SENDING_PAUSED' => ['EMAIL_SENDING_PAUSED'];
        yield 'EMAIL_DOMAIN_SENDING_PAUSED' => ['EMAIL_DOMAIN_SENDING_PAUSED'];
        yield 'EMAIL_DOMAIN_NOT_VERIFIED' => ['EMAIL_DOMAIN_NOT_VERIFIED'];
        yield 'EMAIL_DOMAIN_NOT_FOUND' => ['EMAIL_DOMAIN_NOT_FOUND'];
        yield 'EMAIL_DOMAIN_NOT_ALLOWED' => ['EMAIL_DOMAIN_NOT_ALLOWED'];
        yield 'EMAIL_DOMAIN_ALREADY_REGISTERED' => ['EMAIL_DOMAIN_ALREADY_REGISTERED'];
        yield 'EMAIL_DOMAIN_LIMIT_REACHED' => ['EMAIL_DOMAIN_LIMIT_REACHED'];
        yield 'EMAIL_RECIPIENT_SUPPRESSED' => ['EMAIL_RECIPIENT_SUPPRESSED'];
        yield 'EMAIL_RECIPIENT_NOT_ALLOWED' => ['EMAIL_RECIPIENT_NOT_ALLOWED'];
        yield 'EMAIL_SUPPRESSION_NOT_FOUND' => ['EMAIL_SUPPRESSION_NOT_FOUND'];
        yield 'EMAIL_SUPPRESSION_NOT_REMOVABLE' => ['EMAIL_SUPPRESSION_NOT_REMOVABLE'];
    }
}
