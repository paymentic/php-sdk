<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Symfony\DependencyInjection;

use Paymentic\Sdk\Environment;
use Paymentic\Sdk\PaymenticClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;

final class PaymenticExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $environment = $config['sandbox'] ? Environment::SANDBOX : Environment::PRODUCTION;

        $clientDefinition = new Definition(PaymenticClient::class);
        $clientDefinition->setArguments([
            $config['api_key'],
            new Reference(ClientInterface::class),
            new Reference(RequestFactoryInterface::class),
            new Reference(StreamFactoryInterface::class),
            $environment,
        ]);
        $container->setDefinition(PaymenticClient::class, $clientDefinition);
        $container->setAlias('paymentic', PaymenticClient::class)->setPublic(true);
    }
}
