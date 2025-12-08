<?php

declare(strict_types=1);

namespace Paymentic\Tests\Feature;

use JsonException;
use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionRequest;
use Paymentic\Sdk\Payment\Domain\Enum\TransactionStatus;
use Paymentic\Sdk\Payment\Domain\ValueObject\Customer;
use Paymentic\Sdk\Payment\Domain\ValueObject\Redirect;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Shared\Enum\Currency;
use Paymentic\Sdk\Shared\Exception\BadRequestException;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Sdk\Shared\Exception\ValidationException;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class TransactionFeatureTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[Test]
    public function createsTransactionSuccessfully(): void
    {
        $responseBody = json_encode([
            'data' => [
                'id' => 'ABCD-123-XYZ-9876',
                'redirectUrl' => 'https://pay.paymentic.com/ABCD-123-XYZ-9876?token=abc123',
                'whitelabel' => null,
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 201);

        $request = new CreateTransactionRequest(
            amount: '123.45',
            title: 'Order #12345',
            description: 'Payment for order',
            redirect: new Redirect(
                success: 'https://merchant.example/success',
                failure: 'https://merchant.example/failure',
            ),
            customer: new Customer(
                name: 'John Doe',
                email: 'john@example.com',
            ),
        );

        $response = $client->payment()->transactions()->create('b8e6e2fc', $request);

        $this->assertSame('ABCD-123-XYZ-9876', $response->id);
        $this->assertSame('https://pay.paymentic.com/ABCD-123-XYZ-9876?token=abc123', $response->redirectUrl);
        $this->assertNull($response->whitelabel);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function getsTransactionDetails(): void
    {
        $responseBody = json_encode([
            'data' => [
                'id' => 'ABCD-123-XYZ-9876',
                'status' => 'PAID',
                'amount' => '123.45',
                'currency' => 'PLN',
                'title' => 'Order #12345',
                'commission' => '1.23',
                'description' => 'Payment for order',
                'customer' => [
                    'name' => 'John Doe',
                    'email' => 'john@example.com',
                ],
                'order' => null,
                'billingAddress' => null,
                'shippingAddress' => null,
                'externalReferenceId' => 'EXT-REF-123',
                'redirect' => [
                    'success' => 'https://merchant.example/success',
                    'failure' => 'https://merchant.example/failure',
                ],
                'paymentMethod' => 'BLIK',
                'paymentChannel' => 'blik',
                'whitelabel' => false,
                'cart' => null,
                'autoCapture' => true,
                'isCaptured' => true,
                'capturedAt' => '2024-01-15T12:00:00+00:00',
                'paidAt' => '2024-01-15T11:55:00+00:00',
                'createdAt' => '2024-01-15T11:50:00+00:00',
                'expiresAt' => '2024-01-15T12:50:00+00:00',
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 200);

        $transaction = $client->payment()->transactions()->get('b8e6e2fc', 'ABCD-123-XYZ-9876');

        $this->assertSame('ABCD-123-XYZ-9876', $transaction->id);
        $this->assertSame(TransactionStatus::PAID, $transaction->status);
        $this->assertSame('123.45', $transaction->amount);
        $this->assertSame(Currency::PLN, $transaction->currency);
        $this->assertSame('Order #12345', $transaction->title);
        $this->assertSame('1.23', $transaction->commission);
        $this->assertSame('BLIK', $transaction->paymentMethod);
        $this->assertTrue($transaction->isCaptured);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function capturesTransaction(): void
    {
        $responseBody = json_encode(['data' => []], JSON_THROW_ON_ERROR);
        $mockHttpClient = new MockHttpClient($responseBody, 202);

        $client = PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient($mockHttpClient)
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();

        $client->payment()->transactions()->capture('b8e6e2fc', 'ABCD-123-XYZ-9876');

        $lastRequest = $mockHttpClient->getLastRequest();
        $this->assertNotNull($lastRequest);
        $this->assertSame('PATCH', $lastRequest->getMethod());
        $this->assertStringContainsString('/b8e6e2fc/transactions/ABCD-123-XYZ-9876/capture', (string) $lastRequest->getUri());
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsBadRequestWhenPointNotActive(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'POINT_NOT_ACTIVE',
                    'message' => 'Point not active.',
                    'docsUrl' => 'https://docs.paymentic.com/errors#POINT_NOT_ACTIVE',
                    'details' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 400);

        $this->expectException(BadRequestException::class);

        $request = new CreateTransactionRequest(
            amount: '100.00',
            title: 'Test Order',
        );

        $client->payment()->transactions()->create('inactive1', $request);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsNotFoundWhenTransactionNotFound(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'TRANSACTION_NOT_FOUND',
                    'message' => 'Transaction not found.',
                    'docsUrl' => 'https://docs.paymentic.com/errors#TRANSACTION_NOT_FOUND',
                    'details' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 404);

        $this->expectException(NotFoundException::class);

        $client->payment()->transactions()->get('b8e6e2fc', 'XXXX-XXX-XXX-XXXX');
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsValidationExceptionOnInvalidData(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'The amount field is required.',
                    'docsUrl' => 'https://docs.paymentic.com/errors#VALIDATION_ERROR',
                    'details' => [
                        'field' => 'amount',
                        'messages' => ['The amount field is required.'],
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 422);

        $this->expectException(ValidationException::class);

        $request = new CreateTransactionRequest(
            amount: '',
            title: 'Test',
        );

        $client->payment()->transactions()->create('b8e6e2fc', $request);
    }

    private function createClient(string $responseBody, int $statusCode): \Paymentic\Sdk\PaymenticClient
    {
        return PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient(new MockHttpClient($responseBody, $statusCode))
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();
    }
}
