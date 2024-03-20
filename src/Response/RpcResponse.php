<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Response;

use Nanofelis\JsonRpcBundle\Request\RawRpcRequest;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;

class RpcResponse implements RpcResponseInterface
{
    /**
     * RpcResponse constructor.
     */
    public function __construct(private mixed $data, private int|string|null $id = null)
    {
    }

    /**
     * @return array<string,mixed>
     */
    public function getContent(): array
    {
        return [
            'jsonrpc' => RawRpcRequest::JSON_RPC_VERSION,
            'result' => $this->data,
            'id' => $this->id,
        ];
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function setData(mixed $data): void
    {
        $this->data = $data;
    }
}
