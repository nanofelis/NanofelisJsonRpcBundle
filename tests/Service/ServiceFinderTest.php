<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ServiceLocator;

class ServiceFinderTest extends TestCase
{
    private ServiceFinder $serviceFinder;

    protected function setUp(): void
    {
        // a ServiceLocator only instantiates the entries actually requested, so
        // neverInstantiatedService stays inert for every test but the laziness one
        $this->serviceFinder = new ServiceFinder(new ServiceLocator([
            'mockService' => static fn () => new MockService(),
            'neverInstantiatedService' => static fn () => new NeverInstantiatedService(),
        ]));
    }

    /**
     * @throws RpcMethodNotFoundException
     */
    public function testFind(): void
    {
        $serviceDesc = $this->serviceFinder->find(new RpcRequest(serviceKey: 'mockService', methodKey: 'add'));

        $this->assertInstanceOf(MockService::class, $serviceDesc->getService());
        $this->assertSame('add', $serviceDesc->getMethodName());
    }

    /**
     * @throws RpcMethodNotFoundException
     */
    public function testDescriptorsAreMemoized(): void
    {
        $first = $this->serviceFinder->find(new RpcRequest(serviceKey: 'mockService', methodKey: 'add'));
        $second = $this->serviceFinder->find(new RpcRequest(serviceKey: 'mockService', methodKey: 'add'));

        $this->assertSame($first, $second);
    }

    public function testFindUnknownService(): void
    {
        $this->expectException(RpcMethodNotFoundException::class);

        $this->serviceFinder->find(new RpcRequest(serviceKey: 'unknown', methodKey: 'add'));
    }

    public function testFindUnknownMethod(): void
    {
        $this->expectException(RpcMethodNotFoundException::class);

        $this->serviceFinder->find(new RpcRequest(serviceKey: 'mockService', methodKey: 'unknown'));
    }

    /**
     * @dataProvider provideUnreachableMethod
     */
    public function testFindUnreachableMethod(string $methodKey): void
    {
        $this->expectException(RpcMethodNotFoundException::class);

        $this->serviceFinder->find(new RpcRequest(serviceKey: 'mockService', methodKey: $methodKey));
    }

    public static function provideUnreachableMethod(): \Generator
    {
        yield 'constructor' => ['__construct'];
        yield 'magic method' => ['__toString'];
        yield 'private method' => ['privateMethod'];
    }

    public function testServiceIsResolvedLazily(): void
    {
        NeverInstantiatedService::$instantiated = false;

        $this->serviceFinder->find(new RpcRequest(serviceKey: 'mockService', methodKey: 'add'));

        $this->assertFalse(NeverInstantiatedService::$instantiated);
    }
}
