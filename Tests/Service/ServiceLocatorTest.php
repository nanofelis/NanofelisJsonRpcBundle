<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Request\RpcRequestPayload;
use Nanofelis\JsonRpcBundle\Service\ServiceLocator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceLocatorTest extends TestCase
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
    public function testFind(RpcRequestPayload $payload, ?string $expectedResult, ?string $expectedException = null)
    {
        $serviceLocator = new ServiceLocator($this->services);

        if ($expectedException) {
            $this->expectException($expectedException);
        }

        $this->assertInstanceOf($expectedResult, $serviceLocator->find($payload));
    }

    /**
     * @expectedException \Nanofelis\JsonRpcBundle\Exception\RPCMethodNotFoundException
     */
    public function testMethodNotExist()
    {
        $payload = new RpcRequestPayload();
        $payload->setServiceId('mock');
        $payload->setMethod('unknownMethod');

        $serviceLocator = new ServiceLocator($this->services);

        $this->assertInstanceOf(MockService::class, $serviceLocator->find($payload));
    }

    public function providePayload(): \Generator
    {
        $payload = new RpcRequestPayload();
        $payload->setMethod('add');
        $payload->setServiceId('mock');

        yield [$payload, MockService::class];

        $payloadUnknownService = new RpcRequestPayload();
        $payloadUnknownService->setMethod('add');
        $payloadUnknownService->setServiceId('unknown');

        yield [$payloadUnknownService, null, RpcMethodNotFoundException::class];

        $payloadMethodNotExist = new RpcRequestPayload();
        $payloadMethodNotExist->setMethod('unknown');
        $payloadMethodNotExist->setServiceId('mock');

        yield [$payloadMethodNotExist, null, RpcMethodNotFoundException::class];
    }
}
