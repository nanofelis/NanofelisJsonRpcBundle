<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Request;

use Nanofelis\JsonRpcBundle\Exception\RpcApplicationException;
use Nanofelis\JsonRpcBundle\Exception\RpcInvalidParamsException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\JsonRpcBundle\Response\RpcResponseError;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use Nanofelis\JsonRpcBundle\Tests\Service\MockService;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class RpcRequestHandlerTest extends TestCase
{
    private RpcRequestHandler $requestHandler;

    private NormalizerInterface|MockObject $normalizer;

    private ArgumentResolverInterface|Stub $argumentResolver;

    protected function setUp(): void
    {
        $eventDispatcher = $this->createStub(EventDispatcherInterface::class);
        // the normalizer stays a mock: testNormalizationContext asserts the context it receives
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->argumentResolver = $this->createStub(ArgumentResolverInterface::class);

        $serviceFinder = new ServiceFinder(new ServiceLocator([
            'mockService' => static fn () => new MockService(),
        ]));
        $kernel = $this->createStub(HttpKernelInterface::class);
        // empty stack: exercises the no-incoming-request path
        $this->requestHandler = new RpcRequestHandler($this->argumentResolver, $serviceFinder, $this->normalizer, $eventDispatcher, $kernel, new RequestStack());
    }

    /**
     * @param null $expectedResult
     */
    #[DataProvider('provideRpcRequest')]
    #[AllowMockObjectsWithoutExpectations]
    public function testHandle(RpcRequest $rpcRequest, ?RpcResponse $expectedResult = null, ?RpcResponseError $expectedError = null): void
    {
        $this->argumentResolver->method('getArguments')->willReturn($rpcRequest->getParams() ?? []);
        $this->normalizer->method('normalize')->willReturnArgument(0);

        $response = $this->requestHandler->handle($rpcRequest);

        if ($expectedError) {
            $this->assertSame($expectedError->getContent(), $response->getContent());
        } else {
            $this->assertSame($expectedResult->getContent(), $response->getContent());
        }
    }

    public function testNormalizationContext(): void
    {
        $rpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'returnObject');

        $this->argumentResolver->method('getArguments')->willReturn([]);

        $this->normalizer->expects($this->once())->method('normalize')
            ->with($this->isInstanceOf(\stdClass::class), null, ['test']);

        $this->requestHandler->handle($rpcRequest);
    }

    public static function provideRpcRequest(): \Generator
    {
        $badTypeRpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'add', params: ['arg1' => '5', 'arg2' => 5]);

        yield [$badTypeRpcRequest, null, new RpcResponseError(new RpcInvalidParamsException())];

        $badArgCountRpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'add', params: ['arg1' => 5]);

        yield [$badArgCountRpcRequest, null, new RpcResponseError(new RpcInvalidParamsException())];

        $exceptionRpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'willThrowException');

        yield [$exceptionRpcRequest, null, new RpcResponseError(new RpcApplicationException('it went wrong', 99))];

        $successRpcRequest = new RpcRequest(serviceKey: 'mockService', methodKey: 'add', params: ['arg1' => 5, 'arg2' => 5]);

        yield [$successRpcRequest, new RpcResponse(10), null];
    }
}
