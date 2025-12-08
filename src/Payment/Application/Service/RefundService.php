<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Service;

use Paymentic\Sdk\Payment\Application\Contract\RefundServiceContract;
use Paymentic\Sdk\Payment\Application\DTO\CreateRefundRequest;
use Paymentic\Sdk\Payment\Application\DTO\CreateRefundResponse;
use Paymentic\Sdk\Payment\Application\Mapper\RefundMapper;
use Paymentic\Sdk\Payment\Domain\Entity\Refund;
use Paymentic\Sdk\Shared\Http\HttpClient;

final readonly class RefundService implements RefundServiceContract
{
    public function __construct(
        private HttpClient $httpClient,
    ) {
    }

    public function create(string $pointId, string $transactionId, CreateRefundRequest $request): CreateRefundResponse
    {
        $response = $this->httpClient->post(
            uri: "/payment/points/{$pointId}/transactions/{$transactionId}/refunds",
            data: $request->toArray(),
        );

        return CreateRefundResponse::fromArray($response['data']);
    }

    public function get(string $pointId, string $transactionId, string $refundId): Refund
    {
        $response = $this->httpClient->get(
            uri: "/payment/points/{$pointId}/transactions/{$transactionId}/refunds/{$refundId}",
        );

        return RefundMapper::fromArray($response['data']);
    }
}
