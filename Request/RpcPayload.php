<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

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
