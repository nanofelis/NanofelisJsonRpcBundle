<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\EventListener;

use Nanofelis\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\JsonRpcBundle\EventListener\RpcRequestListener;
use Nanofelis\JsonRpcBundle\Request\RpcRequestPayload;
use Nanofelis\JsonRpcBundle\Tests\Service\MockService;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;

class RpcRequestListenerTest extends TestCase
{
    /**
     * @var RpcRequestListener
     */
    private $subscriber;

    protected function setUp()
    {
        $converterManager = new ParamConverterManager();
        $converterManager->add(new DateTimeParamConverter());

        $this->subscriber = new RpcRequestListener($converterManager);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            RpcBeforeMethodEvent::NAME => 'onRPCBeforeMethodEvent',
        ], $this->subscriber->getSubscribedEvents());
    }

    public function testParamConversionWithDate()
    {
        $payload = new RpcRequestPayload();
        $payload->setMethod('getDateIso');
        $payload->setRequest(Request::create('/fake'));
        $payload->setParams(['date' => '1970-01-01']);

        $event = new RpcBeforeMethodEvent($payload, new MockService());

        $this->subscriber->onRPCBeforeMethodEvent($event);

        $this->assertInstanceOf(\DateTime::class, $payload->getParams()['date']);
    }
    
    public function testParamConversionWithArrayArg()
    {
    }    
    
    public function testParamConversionWithDoctrineEntity()
    {
    }
}
