<?php

declare(strict_types=1);

namespace Paymentic\Tests\Unit\Application\DTO;

use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionRequest;
use Paymentic\Sdk\Payment\Domain\Enum\PaymentMethod;
use Paymentic\Sdk\Payment\Domain\ValueObject\Redirect;
use Paymentic\Sdk\Shared\Enum\Currency;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CreateTransactionRequestTest extends TestCase
{
    #[Test]
    public function convertsMinimalRequestToArray(): void
    {
        $request = new CreateTransactionRequest(
            amount: '100.00',
            title: 'Test Payment'
        );

        $array = $request->toArray();

        $this->assertSame('100.00', $array['amount']);
        $this->assertSame('Test Payment', $array['title']);
        $this->assertArrayNotHasKey('currency', $array);
    }

    #[Test]
    public function convertsFullRequestToArray(): void
    {
        $request = new CreateTransactionRequest(
            amount: '250.50',
            title: 'Order #12345',
            currency: Currency::EUR,
            description: 'Payment for order',
            externalReferenceId: 'EXT-123',
            redirect: new Redirect(
                success: 'https://example.com/success',
                failure: 'https://example.com/failure'
            ),
            paymentMethod: PaymentMethod::CARD,
            autoCapture: true
        );

        $array = $request->toArray();

        $this->assertSame('250.50', $array['amount']);
        $this->assertSame('Order #12345', $array['title']);
        $this->assertSame('EUR', $array['currency']);
        $this->assertSame('Payment for order', $array['description']);
        $this->assertSame('EXT-123', $array['externalReferenceId']);
        $this->assertSame('CARD', $array['paymentMethod']);
        $this->assertTrue($array['autoCapture']);
        $this->assertIsArray($array['redirect']);
    }
}
