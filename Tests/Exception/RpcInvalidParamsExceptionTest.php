<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Exception;

use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidParamsException;
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
