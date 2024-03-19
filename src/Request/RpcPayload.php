<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Nanofelis\JsonRpcBundle\Response\RpcResponseInterface;

class RpcPayload
{
    /**
     * @var RpcRequest[]
     */
    private array $rpcRequests = [];

    /**
     * @var RpcResponseInterface[]
     */
    private array $rpcResponses = [];

    private bool $isBatch = false;

    /**
     * @return RpcResponseInterface[]
     */
    public function getRpcResponses(): array
    {
        return $this->rpcResponses;
    }

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

    public function addRpcResponse(RpcResponseInterface $rpcResponse): void
    {
        $this->rpcResponses[] = $rpcResponse;
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
