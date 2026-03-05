<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Service;

use JsonException;
use Paymentic\Sdk\Payment\Application\Contract\SystemServiceContract;
use Paymentic\Sdk\Payment\Application\DTO\PingResponse;
use Paymentic\Sdk\Shared\Exception\PaymenticException;
use Paymentic\Sdk\Shared\Http\HttpClient;

final readonly class SystemService implements SystemServiceContract
{
    public function __construct(
        private HttpClient $httpClient,
    ) {
    }

    /**
     * @throws PaymenticException
     * @throws JsonException
     */
    public function ping(): PingResponse
    {
        $response = $this->httpClient->get(
            uri: '/payment/ping',
        );

        return PingResponse::fromArray($response['data']);
    }
}
