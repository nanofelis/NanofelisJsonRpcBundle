<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Response;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;

class RpcResponse implements RpcResponseInterface
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * @var mixed|null
     */
    private $id;

    /**
     * RpcResponse constructor.
     *
     * @param mixed      $data
     * @param mixed|null $id
     */
    public function __construct($data, $id = null)
    {
        $this->data = $data;
        $this->id = $id;
    }

    public function getContent(): array
    {
        return [
            'jsonrpc' => RpcRequest::JSON_RPC_VERSION,
            'result' => $this->data,
            'id' => $this->id,
        ];
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}
