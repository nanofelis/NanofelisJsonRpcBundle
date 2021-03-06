<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Request;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcApplicationException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceConfigLoader;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceFinder;
use Nanofelis\Bundle\JsonRpcBundle\Tests\Service\MockService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RpcRequestHandlerTest extends TestCase
{
    /**
     * @var RpcRequestHandler
     */
    private $requestHandler;

    /**
     * @var NormalizerInterface|MockObject
     */
    private $normalizer;

    protected function setUp(): void
    {
        $services = new \ArrayIterator([new MockService()]);
        $serviceConfigLoader = $this->createMock(ServiceConfigLoader::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);

        $serviceFinder = new ServiceFinder($services, $serviceConfigLoader);
        $this->requestHandler = new RpcRequestHandler($serviceFinder, $this->normalizer, $eventDispatcher);
    }

    /**
     * @dataProvider provideRpcRequest
     *
     * @param RpcRequest            $rpcRequest
     * @param null                  $expectedResult
     * @param RpcResponseError|null $expectedError
     */
    public function testHandle(RpcRequest $rpcRequest, $expectedResult = null, RpcResponseError $expectedError = null)
    {
        if ($expectedError) {
            $this->assertSame($expectedResult, $rpcRequest->getResponseError());
        } else {
            $this->normalizer->expects($this->once())->method('normalize')->with($expectedResult, null, []);
        }

        $this->requestHandler->handle($rpcRequest);
    }

    /**
     * @return \Generator
     */
    public function provideRpcRequest(): \Generator
    {
        $badTypeRpcRequest = new RpcRequest();
        $badTypeRpcRequest->setMethodKey('add');
        $badTypeRpcRequest->setServiceKey('mockService');
        $badTypeRpcRequest->setParams(['arg1' => '5', 'arg2' => 5]);

        yield [$badTypeRpcRequest, null, new RpcResponseError(new RpcInvalidRequestException())];

        $badArgCountRpcRequest = new RpcRequest();
        $badArgCountRpcRequest->setMethodKey('add');
        $badArgCountRpcRequest->setServiceKey('mockService');
        $badArgCountRpcRequest->setParams(['arg1' => 5]);

        yield [$badArgCountRpcRequest, null, new RpcResponseError(new RpcInvalidRequestException())];

        $exceptionRpcRequest = new RpcRequest();
        $exceptionRpcRequest->setMethodKey('willThrowException');
        $exceptionRpcRequest->setServiceKey('mockService');

        yield [$exceptionRpcRequest, null, new RpcResponseError(new RpcApplicationException())];

        $successRpcRequest = new RpcRequest();
        $successRpcRequest->setMethodKey('add');
        $successRpcRequest->setServiceKey('mockService');
        $successRpcRequest->setParams(['arg1' => 5, 'arg2' => 5]);

        yield [$successRpcRequest, 10, null];
    }
}
