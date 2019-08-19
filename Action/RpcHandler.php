<?php

namespace Nanofelis\Bundle\JsonRpcBundle\Action;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestParser;
use Symfony\Component\HttpFoundation\Request;

class RpcHandler
{
    /**
     * @var RpcRequestParser
     */
    private $parser;

    /**
     * @var RpcRequestHandler
     */
    private $rpcRequestHandler;

    /**
     * RpcHandler constructor.
     *
     * @param RpcRequestParser  $parser
     * @param RpcRequestHandler $rpcRequestHandler
     */
    public function __construct(
        RpcRequestParser $parser,
        RpcRequestHandler $rpcRequestHandler
    ) {
        $this->parser = $parser;
        $this->rpcRequestHandler = $rpcRequestHandler;
    }

    /**
     * @param Request $request
     *
     * @return RpcPayload
     */
    public function createRpcPayload(Request $request): RpcPayload
    {
        $payload = $this->parser->parse($request);

        foreach ($payload->getRpcRequests() as $rpcRequest) {
            $this->rpcRequestHandler->handle($rpcRequest);
        }

        return $payload;
    }
}
