<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public const int DEFAULT_MAX_BATCH_SIZE = 100;

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('nanofelis_json_rpc');

        $treeBuilder->getRootNode()
            ->children()
                ->integerNode('max_batch_size')
                    ->info('Maximum number of requests accepted in a single batch call. Set to 0 to disable the limit.')
                    ->defaultValue(self::DEFAULT_MAX_BATCH_SIZE)
                    ->min(0)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
