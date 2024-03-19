<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\tests\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\JsonRpcBundle\Service\ServiceDescriptor;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceFinderTest extends TestCase
{
    private MockObject $configLoader;

    private \ArrayIterator $services;

    protected function setUp(): void
    {
        $this->services = new \ArrayIterator([
            'mockService' => new MockService(),
        ]);
    }

    /**
     * @dataProvider providePayload
     *
     * @throws RpcMethodNotFoundException
     */
    public function testFind(RpcRequest $payload, ?string $expectedResult, string $expectedException = null)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $serviceLocator = new ServiceFinder($this->services);

        $this->assertInstanceOf($expectedResult, $serviceLocator->find($payload));
    }

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
