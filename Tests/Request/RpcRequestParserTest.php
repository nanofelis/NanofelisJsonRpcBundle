<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request\Tests;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestParser;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRpcRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RpcRequestParserTest extends TestCase
{
    public function testParse()
    {
        $validator = $this->createMock(ValidatorInterface::class);
        $validator->method('validate')->willReturn(new ConstraintViolationList());
        $parser = new RpcRequestParser($validator);

        $request = Request::create('/', 'GET', [], [], [], [], json_encode([
            'jsonrpc' => '2.0',
            'method' => 'mock.add',
            'params' => [1, 2]
        ]));

        $payload = $parser->parse($request);

        $this->assertInstanceOf(RpcRpcRequest::class, $payload);
    }
}
