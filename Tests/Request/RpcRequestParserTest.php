<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Request;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestParser;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RpcRequestParserTest extends TestCase
{
    private RpcRequestParser $parser;

    protected function setUp(): void
    {
        $this->parser = new RpcRequestParser();
    }

    public function testParsePostRequest()
    {
        $request = Request::create(uri: '/', method: 'POST', content: json_encode([
            'jsonrpc' => '2.0',
            'method' => 'mockService.add',
            'params' => [1, 2],
            'id' => 'test',
        ]));

        $payload = $this->parser->parse($request);

        $this->assertInstanceOf(RpcPayload::class, $payload);
        $rpcRequest = $payload->getRpcRequests()[0];

        $this->assertNull($rpcRequest->getResponse());
        $this->assertSame('add', $rpcRequest->getMethodKey());
        $this->assertSame('mockService', $rpcRequest->getServiceKey());
        $this->assertSame([1, 2], $rpcRequest->getParams());
        $this->assertSame('test', $rpcRequest->getId());
    }

    public function testParseGetRequest()
    {
        $request = Request::create(sprintf('/?%s', http_build_query([
            'jsonrpc' => '2.0',
            'method' => 'mockService.add',
            'params' => [1, 2],
            'id' => 'test',
        ])));

        $payload = $this->parser->parse($request);

        $this->assertInstanceOf(RpcPayload::class, $payload);
        $rpcRequest = $payload->getRpcRequests()[0];

        $this->assertNull($rpcRequest->getResponse());
        $this->assertSame('add', $rpcRequest->getMethodKey());
        $this->assertSame('mockService', $rpcRequest->getServiceKey());
        $this->assertSame(['1', '2'], $rpcRequest->getParams());
        $this->assertSame('test', $rpcRequest->getId());
    }

    public function testParseBadHttpMethod()
    {
        $request = Request::create('/', 'PUT');
        $payload = $this->parser->parse($request);
        $rpcRequest = $payload->getRpcRequests()[0];

        $this->assertInstanceOf(RpcResponseError::class, $rpcRequest->getResponse());
    }

    public function testParseBadInvalidRpcFormat()
    {
        $request = Request::create(uri: '/', method: 'POST', content: json_encode([
            'jsonrpc' => '2.0',
            'wrongFormat' => 'mockService->add',
        ]));
        $payload = $this->parser->parse($request);
        $rpcRequest = $payload->getRpcRequests()[0];

        $this->assertInstanceOf(RpcResponseError::class, $rpcRequest->getResponse());
    }
}
