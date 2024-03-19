<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\tests\Response;

use Nanofelis\JsonRpcBundle\Exception\RpcApplicationException;
use Nanofelis\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\JsonRpcBundle\Responder\RpcResponder;
use Nanofelis\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\JsonRpcBundle\Response\RpcResponseError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RpcResponderTest extends TestCase
{
    private MockObject $eventDispatcher;

    private RpcResponder $responder;

    protected function setUp(): void
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->responder = new RpcResponder($this->eventDispatcher);
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
        $successRequest = new RpcRequest();
        $successRequest->setResponse(new RpcResponse('success', 1));

        $errorRequest = new RpcRequest();
        $errorException = new RpcApplicationException('error', 99);
        $errorException->setData(['details']);
        $errorRequest->setResponse(new RpcResponseError($errorException, 2));

        $payload = new RpcPayload();
        $payload->setIsBatch(true);
        $payload->addRpcRequest($successRequest);
        $payload->addRpcRequest($errorRequest);

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
                        'data' => ['details'],
                    ],
                    'id' => 2,
                ],
            ],
        ];

        $payload = new RpcPayload();
        $payload->addRpcRequest($successRequest);

        yield [$payload, [
            'jsonrpc' => RpcRequest::JSON_RPC_VERSION,
            'result' => 'success',
            'id' => 1,
        ]];
    }
}
