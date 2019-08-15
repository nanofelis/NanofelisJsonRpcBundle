<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Exception;

use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
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
