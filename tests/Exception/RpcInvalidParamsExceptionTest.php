<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\tests\Exception;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcInvalidParamsException;
use PHPUnit\Framework\TestCase;

class RpcInvalidParamsExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $e = new RpcInvalidParamsException();

        $this->assertSame(AbstractRpcException::INVALID_PARAMS, $e->getCode());
        $this->assertSame(AbstractRpcException::MESSAGES[AbstractRpcException::INVALID_PARAMS], $e->getMessage());
    }
}
