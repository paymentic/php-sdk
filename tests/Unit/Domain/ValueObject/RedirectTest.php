<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\ValueObject\Redirect;
use Paymentic\Sdk\Shared\Exception\InvalidValueException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RedirectTest extends TestCase
{
    #[Test]
    public function createsRedirectWithBothUrls(): void
    {
        $redirect = new Redirect(
            success: 'https://example.com/success',
            failure: 'https://example.com/failure'
        );

        $this->assertSame('https://example.com/success', $redirect->success);
        $this->assertSame('https://example.com/failure', $redirect->failure);
    }

    #[Test]
    public function createsRedirectWithOnlySuccessUrl(): void
    {
        $redirect = new Redirect(success: 'https://example.com/success');

        $this->assertSame('https://example.com/success', $redirect->success);
        $this->assertNull($redirect->failure);
    }

    #[Test]
    public function convertsToArrayFilteringNulls(): void
    {
        $redirect = new Redirect(success: 'https://example.com/success');
        $array = $redirect->toArray();

        $this->assertArrayHasKey('success', $array);
        $this->assertArrayNotHasKey('failure', $array);
        $this->assertSame('https://example.com/success', $array['success']);
    }

    #[Test]
    public function throwsExceptionForInvalidSuccessUrl(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid URL for success: "not-a-url"');

        new Redirect(success: 'not-a-url');
    }

    #[Test]
    public function throwsExceptionForInvalidFailureUrl(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid URL for failure: "invalid"');

        new Redirect(failure: 'invalid');
    }

    #[Test]
    public function acceptsHttpUrl(): void
    {
        $redirect = new Redirect(success: 'http://example.com/success');

        $this->assertSame('http://example.com/success', $redirect->success);
    }
}
