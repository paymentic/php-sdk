<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Payment\Application\Service;

use Exception;
use Paymentic\Sdk\Payment\Application\Contract\PointServiceContract;
use Paymentic\Sdk\Payment\Application\Mapper\ChannelMapper;
use Paymentic\Sdk\Payment\Domain\Entity\Channel;
use Paymentic\Sdk\Shared\Http\HttpClient;

final readonly class PointService implements PointServiceContract
{
    public function __construct(
        private HttpClient $httpClient,
    ) {
    }

    /**
     * @return Channel[]
     * @throws Exception
     */
    public function getChannels(string $pointId): array
    {
        $response = $this->httpClient->get(
            uri: "/payment/points/{$pointId}/channels",
        );

        return ChannelMapper::fromArrayCollection($response['data']);
    }
}
