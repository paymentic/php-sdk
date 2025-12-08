<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\ValueObject\Customer;
use Paymentic\Sdk\Shared\Exception\InvalidValueException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CustomerTest extends TestCase
{
    #[Test]
    public function createsWithAllFields(): void
    {
        $customer = new Customer(
            name: 'Jan Kowalski',
            email: 'jan@example.com',
            emailVerified: true,
            phone: '+48123456789',
            phoneVerified: false,
            country: 'PL',
            locale: 'pl_PL',
            ip: '192.168.1.1',
            userAgent: 'Mozilla/5.0',
            fingerprint: 'abc123fingerprint',
        );

        $this->assertSame('Jan Kowalski', $customer->name);
        $this->assertSame('jan@example.com', $customer->email);
        $this->assertTrue($customer->emailVerified);
        $this->assertSame('+48123456789', $customer->phone);
        $this->assertFalse($customer->phoneVerified);
        $this->assertSame('PL', $customer->country);
        $this->assertSame('pl_PL', $customer->locale);
        $this->assertSame('192.168.1.1', $customer->ip);
        $this->assertSame('Mozilla/5.0', $customer->userAgent);
        $this->assertSame('abc123fingerprint', $customer->fingerprint);
    }

    #[Test]
    public function createsWithNoFields(): void
    {
        $customer = new Customer();

        $this->assertNull($customer->name);
        $this->assertNull($customer->email);
        $this->assertNull($customer->emailVerified);
        $this->assertNull($customer->phone);
        $this->assertNull($customer->phoneVerified);
        $this->assertNull($customer->country);
        $this->assertNull($customer->locale);
        $this->assertNull($customer->ip);
        $this->assertNull($customer->userAgent);
        $this->assertNull($customer->fingerprint);
    }

    #[Test]
    public function convertsToArrayWithAllFields(): void
    {
        $customer = new Customer(
            name: 'Test User',
            email: 'test@example.com',
            emailVerified: true,
            phone: '+48987654321',
            phoneVerified: true,
        );

        $array = $customer->toArray();

        $this->assertSame('Test User', $array['name']);
        $this->assertSame('test@example.com', $array['email']);
        $this->assertTrue($array['emailVerified']);
        $this->assertSame('+48987654321', $array['phone']);
        $this->assertTrue($array['phoneVerified']);
    }

    #[Test]
    public function convertsToArrayExcludingNullFields(): void
    {
        $customer = new Customer(
            email: 'only@email.com',
        );

        $array = $customer->toArray();

        $this->assertSame(['email' => 'only@email.com'], $array);
        $this->assertArrayNotHasKey('name', $array);
        $this->assertArrayNotHasKey('phone', $array);
    }

    #[Test]
    public function convertsToEmptyArrayWhenNoFields(): void
    {
        $customer = new Customer();

        $this->assertSame([], $customer->toArray());
    }

    #[Test]
    public function handlesBooleanFieldsCorrectly(): void
    {
        $customer = new Customer(
            emailVerified: false,
            phoneVerified: false,
        );

        $array = $customer->toArray();

        $this->assertArrayHasKey('emailVerified', $array);
        $this->assertArrayHasKey('phoneVerified', $array);
        $this->assertFalse($array['emailVerified']);
        $this->assertFalse($array['phoneVerified']);
    }

    #[Test]
    public function throwsExceptionForInvalidEmail(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid email format: "invalid-email"');

        new Customer(email: 'invalid-email');
    }

    #[Test]
    public function throwsExceptionForInvalidIp(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid IP address: "not-an-ip"');

        new Customer(ip: 'not-an-ip');
    }

    #[Test]
    public function throwsExceptionForInvalidCountryCode(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid country code (expected ISO 3166-1 alpha-2): "POL"');

        new Customer(country: 'POL');
    }

    #[Test]
    public function acceptsValidIpv6Address(): void
    {
        $customer = new Customer(ip: '2001:0db8:85a3:0000:0000:8a2e:0370:7334');

        $this->assertSame('2001:0db8:85a3:0000:0000:8a2e:0370:7334', $customer->ip);
    }
}
