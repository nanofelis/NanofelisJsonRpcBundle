<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\tests\Exception;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcInternalException;
use PHPUnit\Framework\TestCase;

class RpcInternalExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $e = new RpcInternalException();

        $this->assertSame(AbstractRpcException::INTERNAL, $e->getCode());
        $this->assertSame(AbstractRpcException::MESSAGES[AbstractRpcException::INTERNAL], $e->getMessage());
    }
}
