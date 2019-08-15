<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Exception;

use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use PHPUnit\Framework\TestCase;

class RpcInvalidRequestExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $e = new RpcInvalidRequestException();

        $this->assertSame(AbstractRpcException::INVALID_REQUEST, $e->getCode());
        $this->assertSame(AbstractRpcException::MESSAGES[AbstractRpcException::INVALID_REQUEST], $e->getMessage());
    }
}
