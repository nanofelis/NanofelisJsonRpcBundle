<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class NanofelisJsonRpcExtension extends Extension
{
    /**
     * @param array<int|string,mixed> $configs
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/../config'));
        $loader->load('services.php');

        // resolved by convention from this extension's namespace, so an override of
        // getConfiguration() is honored here too; the fallback only guards the return type
        $configuration = $this->getConfiguration($configs, $container) ?? new Configuration();

        /** @var array{max_batch_size: int} $config */
        $config = $this->processConfiguration($configuration, $configs);

        $container->getDefinition('nanofelis_json_rpc.request.parser')
            ->replaceArgument(0, $config['max_batch_size']);
    }
}
