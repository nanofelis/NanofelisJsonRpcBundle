<?php

namespace Nanofelis\Bundle\JsonRpcBundle\Event;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponse;

class RpcBeforeResponseEvent
{
    /**
     * @var RpcRequest
     */
    private $rpcRequest;

    /**
     * RpcBeforeResponseEvent constructor.
     *
     * @param RpcResponse $response
     */
    public function __construct(RpcRequest $payload)
    {
        $this->rpcRequest = $payload;
    }

    /**
     * @return RpcRequest
     */
    public function getRpcRequest(): RpcRequest
    {
        return $this->rpcRequest;
    }
}