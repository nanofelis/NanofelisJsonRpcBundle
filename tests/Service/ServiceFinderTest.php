<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Exception\RpcServiceKeyMissingException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use PHPUnit\Framework\TestCase;

class ServiceFinderTest extends TestCase
{
    /**
     * @var \ArrayIterator<string,MockService>
     */
    private \ArrayIterator $services;

    protected function setUp(): void
    {
        $this->services = new \ArrayIterator([
            'mockService' => new MockService(),
        ]);
    }

    /**
     * @throws RpcMethodNotFoundException
     */
    public function testFind(): void
    {
        $serviceLocator = new ServiceFinder($this->services);
        $serviceDesc = $serviceLocator->find(new RpcRequest(serviceKey: 'mockService', methodKey: 'add'));

        $this->assertInstanceOf(MockService::class, $serviceDesc->getService());
        $this->assertSame('add', $serviceDesc->getMethodName());
    }

    public function testFindUnknownService(): void
    {
        $serviceLocator = new ServiceFinder($this->services);

        $this->expectException(RpcMethodNotFoundException::class);

        $serviceLocator->find(new RpcRequest(serviceKey: 'unknown', methodKey: 'add'));
    }

    public function testFindUnknownMethod(): void
    {
        $serviceLocator = new ServiceFinder($this->services);

        $this->expectException(RpcMethodNotFoundException::class);

        $serviceLocator->find(new RpcRequest(serviceKey: 'mockService', methodKey: 'unknown'));
    }

    public function testServiceWithoutAttributeThrowsException(): void
    {
        $services = new \ArrayIterator([
            'brokenService' => new class() {},
        ]);

        $this->expectException(RpcServiceKeyMissingException::class);

        new ServiceFinder($services);
    }
}
