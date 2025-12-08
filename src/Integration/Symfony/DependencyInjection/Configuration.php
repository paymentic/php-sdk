<?php

declare(strict_types=1);

namespace Paymentic\Sdk\Integration\Symfony\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('paymentic');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('api_key')
            ->isRequired()
            ->cannotBeEmpty()
            ->end()
            ->booleanNode('sandbox')
            ->defaultFalse()
            ->end()
            ->scalarNode('webhook_secret')
            ->defaultNull()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
