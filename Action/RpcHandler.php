<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Action;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestParser;
use Symfony\Component\HttpFoundation\Request;

class RpcHandler
{
    /**
     * RpcHandler constructor.
     */
    public function __construct(private RpcRequestParser $parser, private RpcRequestHandler $rpcRequestHandler)
    {
    }

    public function createRpcPayload(Request $request): RpcPayload
    {
        $payload = $this->parser->parse($request);

        foreach ($payload->getRpcRequests() as $rpcRequest) {
            $this->rpcRequestHandler->handle($rpcRequest);
        }

        return $payload;
    }
}
