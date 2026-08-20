<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\DependencyInjection\Compiler;

use Nanofelis\JsonRpcBundle\Attribute\JsonRpcService;
use Nanofelis\JsonRpcBundle\DependencyInjection\Compiler\RpcServicePass;
use Nanofelis\JsonRpcBundle\Exception\RpcServiceKeyMissingException;
use Nanofelis\JsonRpcBundle\NanofelisJsonRpcBundle;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use Nanofelis\JsonRpcBundle\Tests\Service\MockService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class RpcServicePassTest extends TestCase
{
    private function createContainer(): ContainerBuilder
    {
        $container = new ContainerBuilder();
        $container->register('nanofelis_json_rpc.service.finder', ServiceFinder::class)
            ->setArguments([null])
            ->setPublic(true);

        return $container;
    }

    public function testTaggedServicesAreIndexedByAttributeKey(): void
    {
        $container = $this->createContainer();
        $container->register(MockService::class, MockService::class)->addTag(RpcServicePass::TAG);

        (new RpcServicePass())->process($container);
        $container->compile();

        $locator = $container->get('nanofelis_json_rpc.service.finder');

        $this->assertInstanceOf(ServiceFinder::class, $locator);
    }

    public function testServiceWithoutAttributeFailsAtCompileTime(): void
    {
        $container = $this->createContainer();
        $container->register(ServiceWithoutAttribute::class, ServiceWithoutAttribute::class)
            ->addTag(RpcServicePass::TAG);

        $this->expectException(RpcServiceKeyMissingException::class);

        (new RpcServicePass())->process($container);
    }

    public function testDuplicateServiceKeyFailsAtCompileTime(): void
    {
        $container = $this->createContainer();
        $container->register('first', DuplicateKeyServiceA::class)->addTag(RpcServicePass::TAG);
        $container->register('second', DuplicateKeyServiceB::class)->addTag(RpcServicePass::TAG);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Duplicate json-rpc service key "duplicated"/');

        (new RpcServicePass())->process($container);
    }

    public function testPassIsANoopWithoutTheFinderService(): void
    {
        $container = new ContainerBuilder();

        (new RpcServicePass())->process($container);

        $this->assertFalse($container->hasDefinition('nanofelis_json_rpc.service.finder'));
    }

    /**
     * An autoconfigured service carrying #[JsonRpcService] must be discovered without a manual tag.
     * Goes through the real bundle build() so the registered attribute configurator is exercised.
     */
    public function testAttributeAutoconfigurationTagsTheService(): void
    {
        $container = $this->createContainer();
        (new NanofelisJsonRpcBundle())->build($container);

        $container->register(MockService::class, MockService::class)
            ->setAutoconfigured(true)
            ->setPublic(true);

        $container->compile();

        $this->assertTrue(
            $container->hasDefinition(MockService::class),
            'the attributed service should survive compilation via autoconfiguration',
        );
        $this->assertInstanceOf(ServiceFinder::class, $container->get('nanofelis_json_rpc.service.finder'));
    }
}

class ServiceWithoutAttribute
{
}

#[JsonRpcService('duplicated')]
class DuplicateKeyServiceA
{
}

#[JsonRpcService('duplicated')]
class DuplicateKeyServiceB
{
}
