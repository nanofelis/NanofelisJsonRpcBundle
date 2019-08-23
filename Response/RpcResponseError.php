<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Response;

use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;

class RpcResponseError implements RpcResponseInterface
{
    /**
     * @var AbstractRpcException
     */
    private $rpcException;

    /**
     * @var mixed|null
     */
    private $id;

    /**
     * RpcResponseError constructor.
     *
     * @param AbstractRpcException $rpcException
     * @param mixed|null           $id
     */
    public function __construct(AbstractRpcException $rpcException, $id = null)
    {
        $this->rpcException = $rpcException;
        $this->id = $id;
    }

    public function getContent(): array
    {
        return [
            'jsonrpc' => RpcRequest::JSON_RPC_VERSION,
            'error' => [
                'code' => $this->rpcException->getCode(),
                'message' => $this->rpcException->getMessage(),
                'data' => $this->rpcException->getData(),
            ],
            'id' => $this->id,
        ];
    }

    /**
     * @return AbstractRpcException
     */
    public function getRpcException(): AbstractRpcException
    {
        return $this->rpcException;
    }

    /**
     * @param AbstractRpcException $rpcException
     */
    public function setRpcException(AbstractRpcException $rpcException): void
    {
        $this->rpcException = $rpcException;
    }
}
