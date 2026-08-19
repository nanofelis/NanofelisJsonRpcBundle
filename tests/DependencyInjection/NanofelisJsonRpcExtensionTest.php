<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\DependencyInjection;

use Nanofelis\JsonRpcBundle\DependencyInjection\Configuration;
use Nanofelis\JsonRpcBundle\DependencyInjection\NanofelisJsonRpcExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class NanofelisJsonRpcExtensionTest extends TestCase
{
    private function loadWith(array $configs): ContainerBuilder
    {
        $container = new ContainerBuilder();
        (new NanofelisJsonRpcExtension())->load($configs, $container);

        return $container;
    }

    public function testMaxBatchSizeDefaultsToConfiguredValue(): void
    {
        $container = $this->loadWith([]);

        $this->assertSame(
            Configuration::DEFAULT_MAX_BATCH_SIZE,
            $container->getDefinition('nanofelis_json_rpc.request.parser')->getArgument(0),
        );
    }

    public function testMaxBatchSizeCanBeOverridden(): void
    {
        $container = $this->loadWith([['max_batch_size' => 5]]);

        $this->assertSame(5, $container->getDefinition('nanofelis_json_rpc.request.parser')->getArgument(0));
    }

    public function testMaxBatchSizeZeroDisablesTheLimit(): void
    {
        $container = $this->loadWith([['max_batch_size' => 0]]);

        $this->assertSame(0, $container->getDefinition('nanofelis_json_rpc.request.parser')->getArgument(0));
    }

    public function testNegativeMaxBatchSizeIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);

        $this->loadWith([['max_batch_size' => -1]]);
    }
}
