<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\EventListener;

use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\Bundle\JsonRpcBundle\EventListener\RpcBeforeMethodListener;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceDescriptor;
use Nanofelis\Bundle\JsonRpcBundle\Tests\Service\MockService;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RpcBeforeMethodListenerTest extends TestCase
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var RpcBeforeMethodListener
     */
    private $subscriber;

    protected function setUp(): void
    {
        $converterManager = new ParamConverterManager();
        $converterManager->add(new DateTimeParamConverter());
        $requestStack = $this->createMock(RequestStack::class);
        $request = Request::create('/fake');
        $requestStack->method('getCurrentRequest')->willReturn($request);

        $this->request = $request;
        $this->subscriber = new RpcBeforeMethodListener($requestStack, $converterManager);
    }

    /**
     * @throws RpcInvalidRequestException
     * @throws RpcMethodNotFoundException
     */
    public function testParamConversionWithDate()
    {
        $rpcRequest = new RpcRequest();
        $rpcRequest->setMethodKey('getDateIso');
        $rpcRequest->setParams(['date' => '1970-01-01']);
        $serviceDescriptor = new ServiceDescriptor(new MockService(), 'getDateIso');

        $event = new RpcBeforeMethodEvent($rpcRequest, $serviceDescriptor);

        $this->subscriber->convertParams($event);

        $this->assertInstanceOf(\DateTime::class, $this->request->attributes->get('date'));
        $this->assertInstanceOf(\DateTime::class, $rpcRequest->getParams()['date']);
    }

    /**
     * Test Array params are excluded from the param conversion.
     *
     * @throws RpcInvalidRequestException
     * @throws RpcMethodNotFoundException
     */
    public function testParamConversionWithArrayArg()
    {
        $rpcRequest = new RpcRequest();
        $rpcRequest->setMethodKey('testArrayParam');
        $rpcRequest->setParams([1, [2, 3, 4]]);
        $serviceDescriptor = new ServiceDescriptor(new MockService(), 'testArrayParam');

        $event = new RpcBeforeMethodEvent($rpcRequest, $serviceDescriptor);

        $this->subscriber->convertParams($event);

        $this->assertSame([1, [2, 3, 4]], $rpcRequest->getParams());
        $this->assertSame([1], $this->request->attributes->all());
    }
}
