<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\tests\Request;

use Nanofelis\JsonRpcBundle\Exception\RpcApplicationException;
use Nanofelis\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\JsonRpcBundle\Response\RpcResponseError;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use Nanofelis\JsonRpcBundle\tests\Service\MockService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RpcRequestHandlerTest extends TestCase
{
    private RpcRequestHandler $requestHandler;

    private NormalizerInterface|MockObject $normalizer;

    private ArgumentResolverInterface|MockObject $argumentResolver;

    protected function setUp(): void
    {
        $services = new \ArrayIterator(['mockService' => new MockService()]);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->argumentResolver = $this->createMock(ArgumentResolverInterface::class);

        $serviceFinder = new ServiceFinder($services);
        $this->requestHandler = new RpcRequestHandler($this->argumentResolver, $serviceFinder, $this->normalizer, $eventDispatcher);
    }

    /**
     * @dataProvider provideRpcRequest
     *
     * @param null $expectedResult
     */
    public function testHandle(RpcRequest $rpcRequest, $expectedResult = null, RpcResponseError $expectedError = null)
    {
        $this->argumentResolver->method('getArguments')->willReturn($rpcRequest->getParams() ?? []);

        if ($expectedError) {
            $this->assertSame($expectedResult, $rpcRequest->getResponse());
        } else {
            $this->normalizer->expects($this->once())->method('normalize')->with($expectedResult, null, []);
        }

        $this->requestHandler->handle($rpcRequest);
    }

    public function testNormalizationContext()
    {
        $rpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'returnObject');

        $this->argumentResolver->method('getArguments')->willReturn([]);

        $this->normalizer->expects($this->once())->method('normalize')
            ->with($this->isInstanceOf(\stdClass::class), null, ['test']);

        $this->requestHandler->handle($rpcRequest);
    }

    public function provideRpcRequest(): \Generator
    {
        $badTypeRpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'add');
        $badTypeRpcRequest->setParams(['arg1' => '5', 'arg2' => 5]);

        yield [$badTypeRpcRequest, null, new RpcResponseError(new RpcInvalidRequestException())];

        $badArgCountRpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'add');
        $badArgCountRpcRequest->setParams(['arg1' => 5]);

        yield [$badArgCountRpcRequest, null, new RpcResponseError(new RpcInvalidRequestException())];

        $exceptionRpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'willThrowException');

        yield [$exceptionRpcRequest, null, new RpcResponseError(new RpcApplicationException())];

        $successRpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'add');
        $successRpcRequest->setParams(['arg1' => 5, 'arg2' => 5]);

        yield [$successRpcRequest, 10, null];
    }
}
