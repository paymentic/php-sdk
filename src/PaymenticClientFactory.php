<?php

declare(strict_types=1);

namespace Paymentic\Sdk;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use RuntimeException;

class PaymenticClientFactory
{
    private string $apiKey;
    private Environment $environment = Environment::PRODUCTION;
    private ?ClientInterface $httpClient = null;
    private ?RequestFactoryInterface $requestFactory = null;
    private ?StreamFactoryInterface $streamFactory = null;

    protected function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public static function create(string $apiKey): self
    {
        return new self($apiKey);
    }

    public function withEnvironment(Environment $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    public function withSandbox(): self
    {
        $this->environment = Environment::SANDBOX;

        return $this;
    }

    public function withProduction(): self
    {
        $this->environment = Environment::PRODUCTION;

        return $this;
    }

    public function withHttpClient(ClientInterface $client): self
    {
        $this->httpClient = $client;

        return $this;
    }

    public function withRequestFactory(RequestFactoryInterface $requestFactory): self
    {
        $this->requestFactory = $requestFactory;

        return $this;
    }

    public function withStreamFactory(StreamFactoryInterface $streamFactory): self
    {
        $this->streamFactory = $streamFactory;

        return $this;
    }

    public function build(): PaymenticClient
    {
        return new PaymenticClient(
            apiKey: $this->apiKey,
            httpClient: $this->httpClient ?? $this->discoverHttpClient(),
            requestFactory: $this->requestFactory ?? $this->discoverRequestFactory(),
            streamFactory: $this->streamFactory ?? $this->discoverStreamFactory(),
            environment: $this->environment,
        );
    }

    private function discoverHttpClient(): ClientInterface
    {
        if ($this->classExists(Client::class)) {
            return new Client();
        }

        throw new RuntimeException(
            'No PSR-18 HTTP client found. Install guzzlehttp/guzzle or provide your own via withHttpClient()'
        );
    }

    private function discoverRequestFactory(): RequestFactoryInterface
    {
        if ($this->classExists(HttpFactory::class)) {
            return new HttpFactory();
        }

        throw new RuntimeException(
            'No PSR-17 RequestFactory found. Install guzzlehttp/psr7 or provide your own via withRequestFactory()'
        );
    }

    private function discoverStreamFactory(): StreamFactoryInterface
    {
        if ($this->classExists(HttpFactory::class)) {
            return new HttpFactory();
        }

        throw new RuntimeException(
            'No PSR-17 StreamFactory found. Install guzzlehttp/psr7 or provide your own via withStreamFactory()'
        );
    }

    protected function classExists(string $class): bool
    {
        return class_exists($class);
    }
}
