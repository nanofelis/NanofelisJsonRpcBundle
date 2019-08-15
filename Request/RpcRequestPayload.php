<?php


namespace Nanofelis\Bundle\JsonRpcBundle\Request;

class RpcRequestPayload implements \Countable
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

    /**
     * @return RpcRequest[]
     */
    public function getValidRpcRequests(): array
    {
        return array_filter($this->rpcRequests, function(RpcRequest $rpcRequest) {
            return !$rpcRequest->getResponseError();
        });
    }

    /**
     * @param RpcRequest $rpcRequest
     */
    public function addRpcRequest(RpcRequest $rpcRequest): void
    {
        $this->rpcRequests[] = $rpcRequest;
    }

    public function count()
    {
        return count($this->rpcRequests);
    }
}