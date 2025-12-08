<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Domain\ValueObject;

use Paymentic\Sdk\Payment\Domain\ValueObject\Address;
use Paymentic\Sdk\Shared\Exception\InvalidValueException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{
    #[Test]
    public function createsWithAllFields(): void
    {
        $address = new Address(
            firstName: 'Jan',
            lastName: 'Kowalski',
            street: 'Główna',
            building: '10',
            flat: '5A',
            city: 'Warszawa',
            region: 'Mazowieckie',
            postalCode: '00-001',
            state: 'Mazowieckie',
            country: 'PL',
            company: 'Firma Sp. z o.o.',
            phone: '+48123456789',
            birthDate: '1990-01-01',
        );

        $this->assertSame('Jan', $address->firstName);
        $this->assertSame('Kowalski', $address->lastName);
        $this->assertSame('Główna', $address->street);
        $this->assertSame('10', $address->building);
        $this->assertSame('5A', $address->flat);
        $this->assertSame('Warszawa', $address->city);
        $this->assertSame('Mazowieckie', $address->region);
        $this->assertSame('00-001', $address->postalCode);
        $this->assertSame('Mazowieckie', $address->state);
        $this->assertSame('PL', $address->country);
        $this->assertSame('Firma Sp. z o.o.', $address->company);
        $this->assertSame('+48123456789', $address->phone);
        $this->assertSame('1990-01-01', $address->birthDate);
    }

    #[Test]
    public function createsWithNoFields(): void
    {
        $address = new Address();

        $this->assertNull($address->firstName);
        $this->assertNull($address->lastName);
        $this->assertNull($address->street);
        $this->assertNull($address->building);
        $this->assertNull($address->flat);
        $this->assertNull($address->city);
        $this->assertNull($address->region);
        $this->assertNull($address->postalCode);
        $this->assertNull($address->state);
        $this->assertNull($address->country);
        $this->assertNull($address->company);
        $this->assertNull($address->phone);
        $this->assertNull($address->birthDate);
    }

    #[Test]
    public function convertsToArrayWithAllFields(): void
    {
        $address = new Address(
            firstName: 'Anna',
            lastName: 'Nowak',
            street: 'Kwiatowa',
            building: '5',
            city: 'Kraków',
            postalCode: '30-001',
            country: 'PL',
        );

        $array = $address->toArray();

        $this->assertSame('Anna', $array['firstName']);
        $this->assertSame('Nowak', $array['lastName']);
        $this->assertSame('Kwiatowa', $array['street']);
        $this->assertSame('5', $array['building']);
        $this->assertSame('Kraków', $array['city']);
        $this->assertSame('30-001', $array['postalCode']);
        $this->assertSame('PL', $array['country']);
    }

    #[Test]
    public function convertsToArrayExcludingNullFields(): void
    {
        $address = new Address(
            city: 'Gdańsk',
            country: 'PL',
        );

        $array = $address->toArray();

        $this->assertSame([
            'city' => 'Gdańsk',
            'country' => 'PL',
        ], $array);

        $this->assertArrayNotHasKey('firstName', $array);
        $this->assertArrayNotHasKey('lastName', $array);
        $this->assertArrayNotHasKey('street', $array);
    }

    #[Test]
    public function convertsToEmptyArrayWhenNoFields(): void
    {
        $address = new Address();

        $this->assertSame([], $address->toArray());
    }

    #[Test]
    public function throwsExceptionForInvalidCountryCode(): void
    {
        $this->expectException(InvalidValueException::class);
        $this->expectExceptionMessage('Invalid country code (expected ISO 3166-1 alpha-2): "POL"');

        new Address(country: 'POL');
    }

    #[Test]
    public function throwsExceptionForNumericCountryCode(): void
    {
        $this->expectException(InvalidValueException::class);

        new Address(country: '12');
    }

    #[Test]
    public function acceptsValidCountryCode(): void
    {
        $address = new Address(country: 'DE');

        $this->assertSame('DE', $address->country);
    }
}
