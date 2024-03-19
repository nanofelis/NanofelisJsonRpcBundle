<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Action;

use Nanofelis\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\JsonRpcBundle\Request\RpcRequestParser;
use Nanofelis\JsonRpcBundle\Responder\RpcResponder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Rpc
{
    public function __construct(
        private RpcRequestParser $parser,
        private RpcRequestHandler $rpcRequestHandler,
        private RpcResponder $responder,
    )
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $rpcPayload = $this->parser->parse($request);

        foreach ($rpcPayload->getUnhandledRpcRequests() as $rpcRequest) {
            $this->rpcRequestHandler->handle($rpcRequest);
        }

        return ($this->responder)($rpcPayload);
    }
}
