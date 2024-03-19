<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\tests\Exception;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use PHPUnit\Framework\TestCase;

class RpcMethodNotFoundExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $e = new RpcMethodNotFoundException();

        $this->assertSame(AbstractRpcException::METHOD_NOT_FOUND, $e->getCode());
        $this->assertSame(AbstractRpcException::MESSAGES[AbstractRpcException::METHOD_NOT_FOUND], $e->getMessage());
    }
}
