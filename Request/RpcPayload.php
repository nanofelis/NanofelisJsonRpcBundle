<?php

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

class RpcPayload
{
    /**
     * @var RpcRequest[]
     */
    private $rpcRequests = [];

    /**
     * @var bool
     */
    private $isBatch = false;

    /**
     * @return RpcRequest[]
     */
    public function getRpcRequests(): array
    {
        return $this->rpcRequests;
    }

    /**
     * @param RpcRequest $rpcRequest
     */
    public function addRpcRequest(RpcRequest $rpcRequest): void
    {
        $this->rpcRequests[] = $rpcRequest;
    }

    /**
     * @return bool
     */
    public function isBatch(): bool
    {
        return $this->isBatch;
    }

    /**
     * @param bool $isBatch
     */
    public function setIsBatch(bool $isBatch): void
    {
        $this->isBatch = $isBatch;
    }
}
