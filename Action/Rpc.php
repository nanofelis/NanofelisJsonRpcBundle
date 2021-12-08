<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Action;

use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Rpc
{
    public function __construct(private RpcHandler $rpcHandler, private RpcResponder $responder)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $rpcPayload = $this->rpcHandler->createRpcPayload($request);

        return ($this->responder)($rpcPayload);
    }
}
