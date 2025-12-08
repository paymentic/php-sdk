<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Service;

use Exception;
use Paymentic\Sdk\Payment\Application\Contract\TransactionServiceContract;
use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionRequest;
use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionResponse;
use Paymentic\Sdk\Payment\Application\Mapper\TransactionMapper;
use Paymentic\Sdk\Payment\Domain\Entity\Transaction;
use Paymentic\Sdk\Shared\Http\HttpClient;

final readonly class TransactionService implements TransactionServiceContract
{
    public function __construct(
        private HttpClient $httpClient,
    ) {
    }

    public function create(string $pointId, CreateTransactionRequest $request): CreateTransactionResponse
    {
        $response = $this->httpClient->post(
            uri: "/payment/points/{$pointId}/transactions",
            data: $request->toArray(),
        );

        return CreateTransactionResponse::fromArray($response['data']);
    }

    /**
     * @throws Exception
     */
    public function get(string $pointId, string $transactionId): Transaction
    {
        $response = $this->httpClient->get(
            uri: "/payment/points/{$pointId}/transactions/{$transactionId}",
        );

        return TransactionMapper::fromArray($response['data']);
    }

    public function capture(string $pointId, string $transactionId): void
    {
        $this->httpClient->patch(
            uri: "/payment/points/{$pointId}/transactions/{$transactionId}/capture",
        );
    }
}
