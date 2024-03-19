<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Response;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Request\RawRpcRequest;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;

class RpcResponseError implements RpcResponseInterface
{
    /**
     * RpcResponseError constructor.
     */
    public function __construct(private AbstractRpcException $rpcException, private mixed $id = null)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function getContent(): array
    {
        return [
            'jsonrpc' => RawRpcRequest::JSON_RPC_VERSION,
            'error' => [
                'code' => $this->rpcException->getCode(),
                'message' => $this->rpcException->getMessage(),
                'data' => $this->rpcException->getData(),
            ],
            'id' => $this->id,
        ];
    }

    public function getRpcException(): AbstractRpcException
    {
        return $this->rpcException;
    }

    public function setRpcException(AbstractRpcException $rpcException): void
    {
        $this->rpcException = $rpcException;
    }
}
