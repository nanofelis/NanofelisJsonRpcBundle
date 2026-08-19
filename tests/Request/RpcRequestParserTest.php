<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Request;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\JsonRpcBundle\Request\RpcRequestParser;
use Nanofelis\JsonRpcBundle\Response\RpcResponseError;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class RpcRequestParserTest extends TestCase
{
    private RpcRequestParser $parser;

    protected function setUp(): void
    {
        $this->parser = new RpcRequestParser();
    }

    public function testParsePostRequest(): void
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

        $this->assertEmpty($payload->getRpcResponses());
        $this->assertSame('add', $rpcRequest->getMethodKey());
        $this->assertSame('mockService', $rpcRequest->getServiceKey());
        $this->assertSame([1, 2], $rpcRequest->getParams());
        $this->assertSame('test', $rpcRequest->getId());
    }

    public function testParseBadInvalidRpcFormat(): void
    {
        $request = Request::create(uri: '/', method: 'POST', content: json_encode([
            'jsonrpc' => '2.0',
            'wrongFormat' => 'mockService->add',
        ]));
        $payload = $this->parser->parse($request);

        $this->assertInstanceOf(RpcResponseError::class, $payload->getRpcResponses()[0]);
    }

    public function testBatchWithinTheLimitIsAccepted(): void
    {
        $payload = (new RpcRequestParser(maxBatchSize: 2))->parse($this->createBatchRequest(2));

        $this->assertCount(2, $payload->getRpcRequests());
        $this->assertEmpty($payload->getRpcResponses());
    }

    public function testOversizedBatchIsRejected(): void
    {
        $payload = (new RpcRequestParser(maxBatchSize: 2))->parse($this->createBatchRequest(3));

        $this->assertEmpty($payload->getRpcRequests());

        $error = $payload->getRpcResponses()[0];
        $this->assertInstanceOf(RpcResponseError::class, $error);
        $this->assertSame(AbstractRpcException::INVALID_REQUEST, $error->getRpcException()->getCode());
    }

    public function testBatchLimitCanBeDisabled(): void
    {
        $payload = (new RpcRequestParser(maxBatchSize: 0))->parse($this->createBatchRequest(50));

        $this->assertCount(50, $payload->getRpcRequests());
    }

    private function createBatchRequest(int $size): Request
    {
        $batch = array_fill(0, $size, [
            'jsonrpc' => '2.0',
            'method' => 'mockService.add',
            'params' => [1, 2],
            'id' => 'test',
        ]);

        return Request::create(uri: '/', method: 'POST', content: json_encode($batch));
    }
}
