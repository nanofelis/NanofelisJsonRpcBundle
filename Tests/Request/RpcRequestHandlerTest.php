<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request\Tests;

use Nanofelis\JsonRpcBundle\Exception\RpcInternalException;
use Nanofelis\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\JsonRpcBundle\Request\RpcRequestPayload;
use Nanofelis\JsonRpcBundle\Service\ServiceAnnotationReader;
use Nanofelis\JsonRpcBundle\Service\ServiceLocator;
use Nanofelis\JsonRpcBundle\Tests\Service\MockService;
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
        $serviceLocator = new ServiceLocator($services);
        $annotationReader = $this->createMock(ServiceAnnotationReader::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);

        $this->requestHandler = new RpcRequestHandler($serviceLocator, $annotationReader, $eventDispatcher, $this->normalizer);
    }

    /**
     * @dataProvider providePayload
     */
    public function testHandle(RpcRequestPayload $payload, $expectedResult = null, ?string $expectedException = null, ?string $exceptionMessage = null)
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
        $badTypePayload = new RpcRequestPayload();
        $badTypePayload->setMethod('add');
        $badTypePayload->setServiceId('mock');
        $badTypePayload->setParams(['arg1' => '5', 'arg2' => 5]);

        yield [$badTypePayload, null, RpcInvalidRequestException::class, 'bad types'];

        $badArgumentsPayload = new RpcRequestPayload();
        $badArgumentsPayload->setMethod('willThrowBadArguments');
        $badArgumentsPayload->setServiceId('mock');

        yield [$badArgumentsPayload, null, RpcInvalidRequestException::class, 'bad arguments'];

        $exceptionPayload = new RpcRequestPayload();
        $exceptionPayload->setMethod('willThrowException');
        $exceptionPayload->setServiceId('mock');

        yield [$exceptionPayload, null, RpcInternalException::class, 'it went wrong'];

        $exceptionPayload = new RpcRequestPayload();
        $exceptionPayload->setMethod('willThrowPhpError');
        $exceptionPayload->setServiceId('mock');

        yield [$exceptionPayload, null, RpcInternalException::class, 'internal server error'];

        $successPayload = new RpcRequestPayload();
        $successPayload->setMethod('add');
        $successPayload->setServiceId('mock');
        $successPayload->setParams(['arg1' => 5, 'arg2' => 5]);

        yield [$successPayload, 10];
    }
}
