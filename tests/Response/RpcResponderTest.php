<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Response;

use Nanofelis\JsonRpcBundle\Exception\RpcApplicationException;
use Nanofelis\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
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
                    'jsonrpc' => RpcRequest::JSON_RPC_VERSION,
                    'result' => 'success',
                    'id' => 1,
                ],
                [
                    'jsonrpc' => RpcRequest::JSON_RPC_VERSION,
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
            'jsonrpc' => RpcRequest::JSON_RPC_VERSION,
            'result' => 'success',
            'id' => 1,
        ]];
    }

    public function testBatchIsEncodedAsAJsonArrayNotAnObject(): void
    {
        // an assoc-array assertion cannot tell a JSON array from a JSON object, so assert on the
        // raw json and on the decoded (non-assoc) type
        $payload = new RpcPayload();
        $payload->setIsBatch(true);
        $payload->addRpcResponse(new RpcResponse('first', 1));
        $payload->addRpcResponse(new RpcResponseError(new RpcApplicationException('bad', 99), 2));

        $content = ($this->responder)($payload)->getContent();

        $this->assertStringStartsWith('[', $content);
        $this->assertIsArray(json_decode($content));
    }
}
