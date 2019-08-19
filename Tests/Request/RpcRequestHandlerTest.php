<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Request;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInternalException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRpcRequest;
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
        $services = new \ArrayIterator([
            new MockService(),
        ]);
        $serviceLocator = new ServiceFinder($services);
        $annotationReader = $this->createMock(ServiceConfigLoader::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);

        $this->requestHandler = new RpcRequestHandler($serviceLocator, $annotationReader, $eventDispatcher, $this->normalizer);
    }

    /**
     * @dataProvider providePayload
     */
    public function testHandle(RpcRpcRequest $payload, $expectedResult = null, ?string $expectedException = null, ?string $exceptionMessage = null)
    {
        if ($expectedException) {
            $this->expectException($expectedException);
            $this->expectExceptionMessageRegExp("/$exceptionMessage/");
        } else {
            $this->normalizer->expects($this->once())->method('normalize')->with($expectedResult);
        }

        $this->requestHandler->handle($payload);
    }

    public function providePayload(): \Generator
    {
        $badTypePayload = new RpcRpcRequest();
        $badTypePayload->setMethod('add');
        $badTypePayload->setServiceId('mock');
        $badTypePayload->setParams(['arg1' => '5', 'arg2' => 5]);

        yield [$badTypePayload, null, RpcInvalidRequestException::class, 'bad types'];

        $badArgumentsPayload = new RpcRpcRequest();
        $badArgumentsPayload->setMethod('willThrowBadArguments');
        $badArgumentsPayload->setServiceId('mock');

        yield [$badArgumentsPayload, null, RpcInvalidRequestException::class, 'bad arguments'];

        $exceptionPayload = new RpcRpcRequest();
        $exceptionPayload->setMethod('willThrowException');
        $exceptionPayload->setServiceId('mock');

        yield [$exceptionPayload, null, RpcInternalException::class, 'it went wrong'];

        $exceptionPayload = new RpcRpcRequest();
        $exceptionPayload->setMethod('willThrowPhpError');
        $exceptionPayload->setServiceId('mock');

        yield [$exceptionPayload, null, RpcInternalException::class, 'internal server error'];

        $successPayload = new RpcRpcRequest();
        $successPayload->setMethod('add');
        $successPayload->setServiceId('mock');
        $successPayload->setParams(['arg1' => 5, 'arg2' => 5]);

        yield [$successPayload, 10];
    }
}
