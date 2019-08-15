<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Exception;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use PHPUnit\Framework\TestCase;

class AbstractRpcExceptionTest extends TestCase
{
    public function testConstructSpecificMessage()
    {
        $e = new RpcInvalidRequestException('custom');

        $this->assertSame('custom', $e->getMessage());
    }
}
