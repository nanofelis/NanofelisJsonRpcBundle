<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Exception;

use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcParseException;
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
