<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Exception;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcParseException;
use PHPUnit\Framework\TestCase;

class RpcParseExceptionTest extends TestCase
{
    public function testConstruct()
    {
        $e = new RpcParseException();

        $this->assertSame(AbstractRpcException::PARSE, $e->getCode());
        $this->assertSame(AbstractRpcException::MESSAGES[AbstractRpcException::PARSE], $e->getMessage());
    }
}
