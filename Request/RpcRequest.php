<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;

class RpcRequest
{
    public const JSON_RPC_VERSION = '2.0';

    public function __construct(
        private mixed $id = null,
        private ?string $method = null,
        private ?string $serviceKey = null,
        private ?string $methodKey = null,
        /**
         * @var array<string,mixed>|null
         */
        private ?array $params = null,
        private RpcResponse|RpcResponseError|null $response = null,
    ) {
    }

    public function getId(): mixed
    {
        return $this->id;
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function getServiceKey(): ?string
    {
        return $this->serviceKey;
    }

    public function setServiceKey(?string $serviceKey): void
    {
        $this->serviceKey = $serviceKey;
    }

    public function getMethodKey(): ?string
    {
        return $this->methodKey;
    }

    public function setMethodKey(?string $methodKey): void
    {
        $this->methodKey = $methodKey;
    }

    /**
     * @return array<string,mixed>|null $params
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param array<string,mixed>|null $params
     */
    public function setParams(?array $params): void
    {
        $this->params = $params;
    }

    public function getResponse(): RpcResponse|RpcResponseError|null
    {
        return $this->response;
    }

    public function setResponse(RpcResponse|RpcResponseError|null $response): void
    {
        $this->response = $response;
    }

    /**
     * @return array<string,mixed>
     */
    public function getResponseContent(): ?array
    {
        return $this->response?->getContent();
    }
}
