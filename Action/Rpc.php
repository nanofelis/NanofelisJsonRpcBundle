<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Action;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestParser;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Rpc
{
    public function __construct(private RpcRequestParser $parser, private RpcRequestHandler $rpcRequestHandler, private RpcResponder $responder)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $rpcPayload = $this->parser->parse($request);

        foreach ($rpcPayload->getRpcRequests() as $rpcRequest) {
            $this->rpcRequestHandler->handle($rpcRequest);
        }

        return ($this->responder)($rpcPayload);
    }
}
