<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Symfony\Mailer;

use GuzzleHttp\Psr7\HttpFactory;
use Paymentic\Sdk\Environment;
use Paymentic\Sdk\Integration\Mailer\PaymenticMailerTransport;
use Paymentic\Sdk\PaymenticClientFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\Mailer\Exception\UnsupportedSchemeException;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PaymenticMailerTransportFactory extends AbstractTransportFactory
{
    public function __construct(
        ?EventDispatcherInterface $eventDispatcher = null,
        ?HttpClientInterface $client = null,
        ?LoggerInterface $logger = null,
        private readonly ?ClientInterface $psrClient = null,
        private readonly ?RequestFactoryInterface $requestFactory = null,
        private readonly ?StreamFactoryInterface $streamFactory = null,
    ) {
        parent::__construct($eventDispatcher, $client, $logger);
    }

    public function create(Dsn $dsn): PaymenticMailerTransport
    {
        if (! in_array($dsn->getScheme(), $this->getSupportedSchemes(), true)) {
            throw new UnsupportedSchemeException($dsn, 'paymentic', $this->getSupportedSchemes());
        }

        $apiKey = $this->getUser($dsn);
        $sandbox = filter_var($dsn->getOption('sandbox', false), FILTER_VALIDATE_BOOLEAN);
        $host = 'default' === $dsn->getHost() ? null : $dsn->getHost();
        $port = $dsn->getPort();
        $baseUrl = null === $host
            ? null
            : sprintf('https://%s%s/v1_2', $host, $port ? ':' . $port : '');

        $builder = PaymenticClientFactory::create($apiKey)
            ->withEnvironment($sandbox ? Environment::SANDBOX : Environment::PRODUCTION);

        if (null !== $baseUrl) {
            $builder->withBaseUrl($baseUrl);
        }

        $psrClient = $this->psrClient;
        $requestFactory = $this->requestFactory;
        $streamFactory = $this->streamFactory;

        if (null === $psrClient && null !== $this->client && null !== ($symfonyPsr = $this->createPsr18Client($this->client))) {
            $psrClient = $symfonyPsr;
            $requestFactory ??= $symfonyPsr;
            $streamFactory ??= $symfonyPsr;
        }

        if (null !== $psrClient) {
            $builder->withHttpClient($psrClient);
        }

        if (null !== $requestFactory) {
            $builder->withRequestFactory($requestFactory);
        }

        if (null !== $streamFactory) {
            $builder->withStreamFactory($streamFactory);
        }

        return new PaymenticMailerTransport(
            $builder->build()->messaging()->email(),
            $this->dispatcher,
            $this->logger,
        );
    }

    private function createPsr18Client(HttpClientInterface $client): ?Psr18Client
    {
        if (! class_exists(Psr18Client::class)) {
            return null;
        }

        try {
            return new Psr18Client($client);
        } catch (\LogicException) {
        }

        if (class_exists(HttpFactory::class)) {
            $factory = new HttpFactory();

            return new Psr18Client($client, $factory, $factory);
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function getSupportedSchemes(): array
    {
        return ['paymentic+api', 'paymentic'];
    }
}
