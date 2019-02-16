<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('nanofelis_json_rest');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('test')->end()
            ->end() // twitter
        ->end()
        ;

        return $treeBuilder;
    }
}
