<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Response;

use Nanofelis\JsonRpcBundle\Exception\RpcApplicationException;
use Nanofelis\JsonRpcBundle\Request\RawRpcRequest;
use Nanofelis\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\JsonRpcBundle\Responder\RpcResponder;
use Nanofelis\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\JsonRpcBundle\Response\RpcResponseError;
use PHPUnit\Framework\TestCase;

class RpcResponderTest extends TestCase
{
    private RpcResponder $responder;

    protected function setUp(): void
    {
        $this->responder = new RpcResponder();
    }

    /**
     * @dataProvider provideRpcPayload
     */
    public function testResponderBatch(RpcPayload $payload, array $expected)
    {
        $jsonResponse = ($this->responder)($payload);

        $this->assertSame($expected, json_decode($jsonResponse->getContent(), true));
    }

    public function provideRpcPayload(): \Generator
    {
        $payload = new RpcPayload();
        $payload->setIsBatch(true);
        $payload->addRpcResponse(new RpcResponse('success', 1));
        $errorException = new RpcApplicationException('error', 99);
        $errorException->setData(['message' => 'details']);
        $payload->addRpcResponse(new RpcResponseError($errorException, 2));

        yield [$payload,
            [
                [
                    'jsonrpc' => RawRpcRequest::JSON_RPC_VERSION,
                    'result' => 'success',
                    'id' => 1,
                ],
                [
                    'jsonrpc' => RawRpcRequest::JSON_RPC_VERSION,
                    'error' => [
                        'code' => 99,
                        'message' => 'error',
                        'data' => ['message' => 'details'],
                    ],
                    'id' => 2,
                ],
            ],
        ];

        $payload = new RpcPayload();
        $payload->addRpcResponse(new RpcResponse('success', 1));

        yield [$payload, [
            'jsonrpc' => RawRpcRequest::JSON_RPC_VERSION,
            'result' => 'success',
            'id' => 1,
        ]];
    }
}
