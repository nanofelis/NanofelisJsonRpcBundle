<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\EventListener;

use Nanofelis\JsonRpcBundle\EventListener\RpcExceptionListener;
use Nanofelis\JsonRpcBundle\Exception\RpcParseException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

class RpcExceptionListenerTest extends TestCase
{
    /**
     * @var RpcExceptionListener
     */
    private $subscriber;

    protected function setUp(): void
    {
        $this->subscriber = new RpcExceptionListener();
    }

    public function testGetSubscribedEvents()
    {
        $this->assertSame([
            'kernel.exception' => 'onKernelException',
        ], $this->subscriber->getSubscribedEvents());
    }

    public function testOnKernelRPCException()
    {
        $event = $this->createMock(GetResponseForExceptionEvent::class);

        $event->method('getException')->willReturn(new RpcParseException());
        $event->expects($this->once())->method('setResponse');

        $this->subscriber->onKernelException($event);
    }

    public function testOnKernelOtherException()
    {
        $event = $this->createMock(GetResponseForExceptionEvent::class);

        $event->method('getException')->willReturn(new \RuntimeException());
        $event->expects($this->never())->method('setResponse');

        $this->subscriber->onKernelException($event);
    }
}
