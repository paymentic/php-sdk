<?php

declare(strict_types=1);

namespace Paymentic\Tests\Feature;

use JsonException;
use Paymentic\Sdk\Payment\Application\DTO\ProcessBlikRequest;
use Paymentic\Sdk\Payment\Domain\Enum\BlikType;
use Paymentic\Sdk\PaymenticClient;
use Paymentic\Sdk\PaymenticClientFactory;
use Paymentic\Sdk\Shared\Exception\BadRequestException;
use Paymentic\Sdk\Shared\Exception\NotFoundException;
use Paymentic\Tests\Support\MockHttpClient;
use Paymentic\Tests\Support\MockRequestFactory;
use Paymentic\Tests\Support\MockStreamFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BlikFeatureTest extends TestCase
{
    /**
     * @throws JsonException
     */
    #[Test]
    public function processesBlikCodeSuccessfully(): void
    {
        $responseBody = json_encode([
            'data' => [
                'actionId' => '01kaqf5trc82bk6cqqanjcjwnq',
                'alias' => null,
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 202);

        $request = new ProcessBlikRequest(
            code: '777123',
            type: BlikType::CODE,
        );

        $response = $client->payment()->blik()->process('b8e6e2fc', 'ABCD-123-XYZ-9876', $request);

        $this->assertSame('01kaqf5trc82bk6cqqanjcjwnq', $response->actionId);
        $this->assertNull($response->alias);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function processesBlikAliasSuccessfully(): void
    {
        $aliasData = [
            'value' => 'alias-token-123',
            'label' => 'mBank',
            'key' => 'abc123',
        ];

        $responseBody = json_encode([
            'data' => [
                'actionId' => '01kaqf5trc82bk6cqqanjcjwnq',
                'alias' => $aliasData,
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 202);

        $request = new ProcessBlikRequest(
            code: 'alias-token-123',
            type: BlikType::ALIAS,
        );

        $response = $client->payment()->blik()->process('b8e6e2fc', 'ABCD-123-XYZ-9876', $request);

        $this->assertSame('01kaqf5trc82bk6cqqanjcjwnq', $response->actionId);
        $this->assertSame($aliasData, $response->alias);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsBadRequestOnInvalidTransactionStatus(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'TRANSACTION_INVALID_STATUS',
                    'message' => 'Transaction invalid status.',
                    'docsUrl' => 'https://docs.paymentic.com/errors#TRANSACTION_INVALID_STATUS',
                    'details' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 400);

        $this->expectException(BadRequestException::class);

        $request = new ProcessBlikRequest(code: '123456');
        $client->payment()->blik()->process('b8e6e2fc', 'ABCD-123-XYZ-9876', $request);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsBadRequestOnBlikProcessingError(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'TRANSACTION_BLIK_PROCESSING_ERROR',
                    'message' => 'Transaction BLIK processing error.',
                    'docsUrl' => 'https://docs.paymentic.com/errors#TRANSACTION_BLIK_PROCESSING_ERROR',
                    'details' => [
                        'blikErrorCode' => 'DECLINED',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 400);

        $this->expectException(BadRequestException::class);

        $request = new ProcessBlikRequest(code: '000000');
        $client->payment()->blik()->process('b8e6e2fc', 'ABCD-123-XYZ-9876', $request);
    }

    #[Test]
    public function throwsBadRequestOnMissingCustomerData(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'TRANSACTION_MISSING_DATA',
                    'message' => 'Transaction missing data.',
                    'docsUrl' => 'https://docs.paymentic.com/errors#TRANSACTION_MISSING_DATA',
                    'details' => [
                        'field' => 'customer.email',
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 400);

        $this->expectException(BadRequestException::class);

        $request = new ProcessBlikRequest(code: '123456');
        $client->payment()->blik()->process('b8e6e2fc', 'ABCD-123-XYZ-9876', $request);
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

        $request = new ProcessBlikRequest(code: '123456');
        $client->payment()->blik()->process('b8e6e2fc', 'XXXX-XXX-XXX-XXXX', $request);
    }

    /**
     * @throws JsonException
     */
    #[Test]
    public function throwsBadRequestOnProviderNotSupported(): void
    {
        $responseBody = json_encode([
            'errors' => [
                [
                    'code' => 'PROVIDER_NOT_SUPPORTED',
                    'message' => 'Provider not supported',
                    'docsUrl' => 'https://docs.paymentic.com/errors#PROVIDER_NOT_SUPPORTED',
                    'details' => null,
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $client = $this->createClient($responseBody, 400);

        $this->expectException(BadRequestException::class);

        $request = new ProcessBlikRequest(code: '123456');
        $client->payment()->blik()->process('b8e6e2fc', 'ABCD-123-XYZ-9876', $request);
    }

    private function createClient(string $responseBody, int $statusCode): PaymenticClient
    {
        return PaymenticClientFactory::create('test-api-key')
            ->withSandbox()
            ->withHttpClient(new MockHttpClient($responseBody, $statusCode))
            ->withRequestFactory(new MockRequestFactory())
            ->withStreamFactory(new MockStreamFactory())
            ->build();
    }
}
