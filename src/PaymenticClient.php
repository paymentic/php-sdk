<?php

declare(strict_types=1);

namespace Paymentic\Sdk;

use Paymentic\Sdk\Payment\PaymentClient;
use Paymentic\Sdk\Shared\Http\HttpClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class PaymenticClient
{
    private PaymentClient $paymentClient;

    public function __construct(
        string $apiKey,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        StreamFactoryInterface $streamFactory,
        Environment $environment = Environment::PRODUCTION,
        ?string $baseUrl = null,
    ) {
        $internalClient = new HttpClient(
            $httpClient,
            $requestFactory,
            $streamFactory,
            $apiKey,
            $baseUrl ?? match ($environment) {
                Environment::PRODUCTION => 'https://api.paymentic.com/v1_2',
                Environment::SANDBOX => 'https://api.sandbox.paymentic.com/v1_2',
            },
        );

        $this->paymentClient = new PaymentClient($internalClient);
    }

    public function payment(): PaymentClient
    {
        return $this->paymentClient;
    }
}
