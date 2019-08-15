<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Service;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceFinder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceFinderTest extends TestCase
{
    /**
     * @var object[]|MockObject[]
     */
    private $services;

    protected function setUp(): void
    {
        $this->services = new \ArrayIterator([
            new MockService(),
        ]);
    }

    /**
     * @dataProvider providePayload
     */
    public function testFind(RpcRpcRequest $payload, ?string $expectedResult, ?string $expectedException = null)
    {
        $serviceLocator = new ServiceFinder($this->services);

        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $this->assertInstanceOf($expectedResult, $serviceLocator->find($payload));
    }

    /**
     * @expectedException \Nanofelis\Bundle\JsonRpcBundle\Exception\RPCMethodNotFoundException
     */
    public function testMethodNotExist()
    {
        $payload = new RpcRpcRequest();
        $payload->setServiceId('mock');
        $payload->setMethod('unknownMethod');

        $serviceLocator = new ServiceFinder($this->services);

        $this->assertInstanceOf(MockService::class, $serviceLocator->find($payload));
    }

    public function providePayload(): \Generator
    {
        $payload = new RpcRpcRequest();
        $payload->setMethod('add');
        $payload->setServiceId('mock');

        yield [$payload, MockService::class];

        $payloadUnknownService = new RpcRpcRequest();
        $payloadUnknownService->setMethod('add');
        $payloadUnknownService->setServiceId('unknown');

        yield [$payloadUnknownService, null, RpcMethodNotFoundException::class];

        $payloadMethodNotExist = new RpcRpcRequest();
        $payloadMethodNotExist->setMethod('unknown');
        $payloadMethodNotExist->setServiceId('mock');

        yield [$payloadMethodNotExist, null, RpcMethodNotFoundException::class];
    }
}
