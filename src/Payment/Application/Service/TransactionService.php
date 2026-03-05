<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Service;

use Exception;
use JsonException;
use Paymentic\Sdk\Payment\Application\Contract\TransactionServiceContract;
use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionRequest;
use Paymentic\Sdk\Payment\Application\DTO\CreateTransactionResponse;
use Paymentic\Sdk\Payment\Application\DTO\ListTransactionsRequest;
use Paymentic\Sdk\Payment\Application\DTO\ListTransactionsResponse;
use Paymentic\Sdk\Payment\Application\Mapper\TransactionListItemMapper;
use Paymentic\Sdk\Payment\Application\Mapper\TransactionMapper;
use Paymentic\Sdk\Payment\Domain\Entity\Transaction;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Paymentic\Sdk\Shared\Http\HttpClient;
use Paymentic\Sdk\Shared\ValueObject\Pagination;

final readonly class TransactionService implements TransactionServiceContract
{
    public function __construct(
        private HttpClient $httpClient,
    ) {
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     */
    public function create(string $pointId, CreateTransactionRequest $request): CreateTransactionResponse
    {
        $response = $this->httpClient->post(
            uri: "/payment/points/{$pointId}/transactions",
            data: $request->toArray(),
        );

        return CreateTransactionResponse::fromArray($response['data']);
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     * @throws Exception
     */
    public function get(string $pointId, string $transactionId): Transaction
    {
        $response = $this->httpClient->get(
            uri: "/payment/points/{$pointId}/transactions/{$transactionId}",
        );

        return TransactionMapper::fromArray($response['data']);
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     */
    public function capture(string $pointId, string $transactionId): void
    {
        $this->httpClient->patch(
            uri: "/payment/points/{$pointId}/transactions/{$transactionId}/capture",
        );
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     * @throws Exception
     */
    public function list(string $pointId, ?ListTransactionsRequest $request = null): ListTransactionsResponse
    {
        $queryString = $request?->toQueryString() ?? '';

        $response = $this->httpClient->get(
            uri: "/payment/points/{$pointId}/transactions{$queryString}",
        );

        return new ListTransactionsResponse(
            data: TransactionListItemMapper::fromArrayCollection($response['data']),
            pagination: Pagination::fromArray($response['pagination']),
        );
    }
}
