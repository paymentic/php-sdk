<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\Enum\AuthorizationType;
use Paymentic\Sdk\Payment\Domain\ValueObject\ChannelAuthorization;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ChannelAuthorizationTest extends TestCase
{
    #[Test]
    public function createsWithSingleType(): void
    {
        $authorization = new ChannelAuthorization(type: [AuthorizationType::REDIRECT]);

        $this->assertCount(1, $authorization->type);
        $this->assertSame(AuthorizationType::REDIRECT, $authorization->type[0]);
    }

    #[Test]
    public function createsWithMultipleTypes(): void
    {
        $authorization = new ChannelAuthorization(type: [
            AuthorizationType::MULTI_FACTOR,
            AuthorizationType::APP_NOTIFICATION,
        ]);

        $this->assertCount(2, $authorization->type);
        $this->assertSame(AuthorizationType::MULTI_FACTOR, $authorization->type[0]);
        $this->assertSame(AuthorizationType::APP_NOTIFICATION, $authorization->type[1]);
    }

    #[Test]
    public function createsWithAllTypes(): void
    {
        $authorization = new ChannelAuthorization(type: [
            AuthorizationType::REDIRECT,
            AuthorizationType::MULTI_FACTOR,
            AuthorizationType::SCAN_CODE,
            AuthorizationType::APP_NOTIFICATION,
        ]);

        $this->assertCount(4, $authorization->type);
        $this->assertContains(AuthorizationType::REDIRECT, $authorization->type);
        $this->assertContains(AuthorizationType::MULTI_FACTOR, $authorization->type);
        $this->assertContains(AuthorizationType::SCAN_CODE, $authorization->type);
        $this->assertContains(AuthorizationType::APP_NOTIFICATION, $authorization->type);
    }

    #[Test]
    public function createsWithEmptyTypes(): void
    {
        $authorization = new ChannelAuthorization(type: []);

        $this->assertCount(0, $authorization->type);
    }

    #[Test]
    public function createsWithNoArguments(): void
    {
        $authorization = new ChannelAuthorization();

        $this->assertCount(0, $authorization->type);
    }
}
