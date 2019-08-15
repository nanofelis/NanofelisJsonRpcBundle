<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Exception;

use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInternalException;
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
