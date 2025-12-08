<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Service;

use Paymentic\Sdk\Payment\Application\Contract\BlikServiceContract;
use Paymentic\Sdk\Payment\Application\DTO\ProcessBlikRequest;
use Paymentic\Sdk\Payment\Application\DTO\ProcessBlikResponse;
use Paymentic\Sdk\Shared\Http\HttpClient;

final readonly class BlikService implements BlikServiceContract
{
    public function __construct(
        private HttpClient $httpClient,
    ) {
    }

    public function process(string $pointId, string $transactionId, ProcessBlikRequest $request): ProcessBlikResponse
    {
        $response = $this->httpClient->post(
            uri: "/payment/points/{$pointId}/transactions/{$transactionId}/blik",
            data: $request->toArray(),
        );

        return ProcessBlikResponse::fromArray($response['data']);
    }
}
