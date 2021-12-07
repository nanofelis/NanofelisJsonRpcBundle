<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Action;

use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class Rpc
{
    /**
     * @var RpcHandler
     */
    private $rpcHandler;

    /**
     * @var RpcResponder
     */
    private $responder;

    public function __construct(RpcHandler $rpcHandler, RpcResponder $responder)
    {
        $this->rpcHandler = $rpcHandler;
        $this->responder = $responder;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $rpcPayload = $this->rpcHandler->createRpcPayload($request);

        return ($this->responder)($rpcPayload);
    }
}
