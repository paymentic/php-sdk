<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Symfony\DependencyInjection;

use Paymentic\Sdk\Environment;
use Paymentic\Sdk\Integration\Symfony\Mailer\PaymenticMailerTransportFactory;
use Paymentic\Sdk\Integration\Symfony\Notifier\PaymenticTransportFactory;
use Paymentic\Sdk\PaymenticClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Mailer\Transport\AbstractTransportFactory as MailerAbstractTransportFactory;
use Symfony\Component\Notifier\Transport\AbstractTransportFactory;

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

        if (class_exists(AbstractTransportFactory::class)) {
            $factory = new Definition(PaymenticTransportFactory::class);
            $factory->setArguments([
                new Reference('event_dispatcher', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference('http_client', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference(ClientInterface::class, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference(RequestFactoryInterface::class, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference(StreamFactoryInterface::class, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            ]);
            $factory->addTag('texter.transport_factory');
            $container->setDefinition(PaymenticTransportFactory::class, $factory);
        }

        if (class_exists(MailerAbstractTransportFactory::class)) {
            $mailerFactory = new Definition(PaymenticMailerTransportFactory::class);
            $mailerFactory->setArguments([
                new Reference('event_dispatcher', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference('http_client', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference('logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference(ClientInterface::class, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference(RequestFactoryInterface::class, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
                new Reference(StreamFactoryInterface::class, ContainerInterface::IGNORE_ON_INVALID_REFERENCE),
            ]);
            $mailerFactory->addTag('mailer.transport_factory');
            $container->setDefinition(PaymenticMailerTransportFactory::class, $mailerFactory);
        }
    }
}
