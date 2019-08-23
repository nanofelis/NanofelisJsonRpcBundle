<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Response;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcApplicationException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponder;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RpcResponderTest extends TestCase
{
    /**
     * @var MockObject
     */
    private $eventDispatcher;

    /**
     * @var RpcResponder
     */
    private $responder;

    protected function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->responder = new RpcResponder($this->eventDispatcher);
    }

    /**
     * @dataProvider provideRpcPayload
     *
     * @param RpcPayload $payload
     * @param array      $expected
     */
    public function testResponderBatch(RpcPayload $payload, array $expected)
    {
        $jsonResponse = ($this->responder)($payload);

        $this->assertSame($expected, json_decode($jsonResponse->getContent(), true));
    }

    /**
     * @return \Generator
     */
    public function provideRpcPayload(): \Generator
    {
        $successRequest = new RpcRequest();
        $successRequest->setResponse(new RpcResponse('success', 1));

        $errorRequest = new RpcRequest();
        $errorException = new RpcApplicationException('error', 99);
        $errorException->setData(['details']);
        $errorRequest->setResponseError(new RpcResponseError($errorException, 2));

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
