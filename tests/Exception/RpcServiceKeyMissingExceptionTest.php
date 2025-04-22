<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Exception;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcServiceKeyMissingException;
use PHPUnit\Framework\TestCase;

class RpcServiceKeyMissingExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $serviceClass = 'App\\Core\\Api\\ZoneService';
        $exception = new RpcServiceKeyMissingException($serviceClass);

        $this->assertSame(AbstractRpcException::INTERNAL, $exception->getCode());
        $this->assertSame(
            'The service "App\\Core\\Api\\ZoneService" must define a key via #[JsonRpcService]',
            $exception->getMessage()
        );
    }
}
