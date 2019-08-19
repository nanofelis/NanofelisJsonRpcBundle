<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\EventListener;

use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\Bundle\JsonRpcBundle\EventListener\RpcRequestListener;
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

class RpcRequestListenerTest extends TestCase
{
    /**
     * @var RpcRequestListener
     */
    private $subscriber;

    protected function setUp(): void
    {
        $converterManager = new ParamConverterManager();
        $converterManager->add(new DateTimeParamConverter());
        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(Request::create('/fake'));

        $this->subscriber = new RpcRequestListener($requestStack, $converterManager);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            RpcBeforeMethodEvent::NAME => 'onRPCBeforeMethodEvent',
        ], $this->subscriber->getSubscribedEvents());
    }

    /**
     * @throws RpcInvalidRequestException
     * @throws RpcMethodNotFoundException
     */
    public function testParamConversionWithDate()
    {
        $payload = new RpcRequest();
        $payload->setMethodKey('getDateIso');
        $payload->setParams(['date' => '1970-01-01']);
        $serviceDescriptor = new ServiceDescriptor(new MockService(), 'getDateIso');

        $event = new RpcBeforeMethodEvent($payload, $serviceDescriptor);

        $this->subscriber->convertParams($event);

        $this->assertInstanceOf(\DateTime::class, $payload->getParams()['date']);
    }

    public function testParamConversionWithArrayArg()
    {
    }

    public function testParamConversionWithDoctrineEntity()
    {
    }
}
