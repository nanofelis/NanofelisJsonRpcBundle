<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class NanofelisJsonRpcExtension extends Extension implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));
        $loader->load('services.xml');
    }

    public function process(ContainerBuilder $container)
    {
        $container->getDefinition('validator.builder')->addMethodCall('addXmlMapping', [__DIR__.'/../Resources/config/validator/rpc_request.xml']);
    }
}
