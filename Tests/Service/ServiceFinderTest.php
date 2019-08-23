<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Service;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceConfigLoader;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceDescriptor;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceFinderTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $configLoader;

    /**
     * @var \ArrayIterator
     */
    private $services;

    protected function setUp(): void
    {
        $this->configLoader = $this->createMock(ServiceConfigLoader::class);
        $this->services = new \ArrayIterator([
            new MockService(),
        ]);
    }

    /**
     * @dataProvider providePayload
     *
     * @param RpcRequest  $payload
     * @param string|null $expectedResult
     * @param string|null $expectedException
     *
     * @throws RpcMethodNotFoundException
     */
    public function testFind(RpcRequest $payload, ?string $expectedResult, ?string $expectedException = null)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        } else {
            $this->configLoader->expects($this->once())->method('loadConfig');
        }

        $serviceLocator = new ServiceFinder($this->services, $this->configLoader);

        $this->assertInstanceOf($expectedResult, $serviceLocator->find($payload));
    }

    /**
     * @return \Generator
     */
    public function providePayload(): \Generator
    {
        $rpcRequest = new RpcRequest();
        $rpcRequest->setMethodKey('add');
        $rpcRequest->setServiceKey('mockService');

        yield [$rpcRequest, ServiceDescriptor::class];

        $rpcRequestUnknownService = new RpcRequest();
        $rpcRequestUnknownService->setMethodKey('add');
        $rpcRequestUnknownService->setServiceKey('unknown');

        yield [$rpcRequestUnknownService, null, RpcMethodNotFoundException::class];

        $rpcRequestUnknownMethod = new RpcRequest();
        $rpcRequestUnknownMethod->setMethodKey('unknown');
        $rpcRequestUnknownMethod->setServiceKey('mockService');

        yield [$rpcRequestUnknownMethod, null, RpcMethodNotFoundException::class];
    }
}
