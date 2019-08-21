<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Exception;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcApplicationException;
use PHPUnit\Framework\TestCase;

class RpcApplicationExceptionTest extends TestCase
{
    public function testConstructInvalidCode()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('application exception code should be outside range -32099 to -32000, given: -32098');

        new RpcApplicationException('app error', RpcApplicationException::CODE_RANGE[0] + 1);
    }
}
