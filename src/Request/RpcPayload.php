<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

class RpcPayload
{
    /**
     * @var RpcRequest[]
     */
    private array $rpcRequests = [];

    private bool $isBatch = false;

    /**
     * @return RpcRequest[]
     */
    public function getRpcRequests(): array
    {
        return $this->rpcRequests;
    }

    /**
     * @return RpcRequest[]
     */
    public function getUnhandledRpcRequests(): array
    {
        return array_filter($this->rpcRequests, fn(RpcRequest $rpcRequest) => !$rpcRequest->getResponse());
    }

    public function addRpcRequest(RpcRequest $rpcRequest): void
    {
        $this->rpcRequests[] = $rpcRequest;
    }

    public function isBatch(): bool
    {
        return $this->isBatch;
    }

    public function setIsBatch(bool $isBatch): void
    {
        $this->isBatch = $isBatch;
    }
}
