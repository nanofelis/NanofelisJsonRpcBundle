<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\JsonRpcBundle\Service\ServiceDescriptor;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @dataProvider providePayload
     *
     * @throws RpcMethodNotFoundException
     */
    public function testFind(RpcRequest $payload, ?string $expectedResult, string $expectedException = null): void
    {
        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $serviceLocator = new ServiceFinder($this->services);

        $this->assertInstanceOf($expectedResult, $serviceLocator->find($payload));
    }

    public function providePayload(): \Generator
    {
        $rpcRequest = new RpcRequest(serviceKey: 'add', methodKey: 'mockService');

        yield [$rpcRequest, ServiceDescriptor::class];

        $rpcRequestUnknownService = new RpcRequest(serviceKey: 'unknown', methodKey: 'add');

        yield [$rpcRequestUnknownService, null, RpcMethodNotFoundException::class];

        $rpcRequestUnknownMethod = new RpcRequest(serviceKey: 'add', methodKey: 'unknown');

        yield [$rpcRequestUnknownMethod, null, RpcMethodNotFoundException::class];
    }
}
