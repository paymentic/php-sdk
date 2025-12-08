<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\Enum;

use Paymentic\Sdk\Payment\Domain\Enum\AuthorizationType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ValueError;

final class AuthorizationTypeTest extends TestCase
{
    #[Test]
    public function hasRedirectCase(): void
    {
        $type = AuthorizationType::REDIRECT;

        $this->assertSame('REDIRECT', $type->value);
    }

    #[Test]
    public function hasMultiFactorCase(): void
    {
        $type = AuthorizationType::MULTI_FACTOR;

        $this->assertSame('MULTI_FACTOR', $type->value);
    }

    #[Test]
    public function hasScanCodeCase(): void
    {
        $type = AuthorizationType::SCAN_CODE;

        $this->assertSame('SCAN_CODE', $type->value);
    }

    #[Test]
    public function hasAppNotificationCase(): void
    {
        $type = AuthorizationType::APP_NOTIFICATION;

        $this->assertSame('APP_NOTIFICATION', $type->value);
    }

    #[Test]
    public function createsFromString(): void
    {
        $redirect = AuthorizationType::from('REDIRECT');
        $multiFactor = AuthorizationType::from('MULTI_FACTOR');
        $scanCode = AuthorizationType::from('SCAN_CODE');
        $appNotification = AuthorizationType::from('APP_NOTIFICATION');

        $this->assertSame(AuthorizationType::REDIRECT, $redirect);
        $this->assertSame(AuthorizationType::MULTI_FACTOR, $multiFactor);
        $this->assertSame(AuthorizationType::SCAN_CODE, $scanCode);
        $this->assertSame(AuthorizationType::APP_NOTIFICATION, $appNotification);
    }

    #[Test]
    public function throwsOnInvalidValue(): void
    {
        $this->expectException(ValueError::class);

        AuthorizationType::from('INVALID');
    }
}
