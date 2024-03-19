<?php
declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Nanofelis\JsonRpcBundle\Exception\RpcInvalidRequestException;

class RawRpcRequest
{
    public const JSON_RPC_VERSION = '2.0';

    /**
     * @throws RpcInvalidRequestException
     */
    public function __construct(
        private string $method,
        private string $jsonrpc = self::JSON_RPC_VERSION,
        private int|string|null $id = null,
        /**
         * @var array<string,mixed>|null
         */
        private ?array $params = null,
    )
    {
        if ($this->jsonrpc !== self::JSON_RPC_VERSION) {
            throw new RpcInvalidRequestException();
        }
    }

    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getId(): int|string|null
    {
        return $this->id;
    }

    /**
     * @return array<string,mixed>
     */
    public function getParams(): ?array
    {
        return $this->params;
    }
}
