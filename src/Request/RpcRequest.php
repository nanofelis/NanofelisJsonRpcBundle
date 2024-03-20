<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

class RpcRequest
{
    public const JSON_RPC_VERSION = '2.0';

    public function __construct(
        private string $serviceKey,
        private string $methodKey,
        private string|int|null $id = null,
        /**
         * @var array<string,mixed>|null
         */
        private ?array $params = null,
    ) {
    }

    public function getId(): string|int|null
    {
        return $this->id;
    }

    public function getServiceKey(): string
    {
        return $this->serviceKey;
    }

    public function getMethodKey(): string
    {
        return $this->methodKey;
    }

    /**
     * @return array<string,mixed>|null $params
     */
    public function getParams(): ?array
    {
        return $this->params;
    }
}
