<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Nanofelis\JsonRpcBundle\Exception\RpcInvalidRequestException;

class RpcRequest
{
    public function __construct(
        private string $serviceKey,
        private string $methodKey,
        private null|string|int $id = null,
        /**
         * @var array<string,mixed>|null
         */
        private ?array $params = null,
    ) {
    }

    /**
     * @throws RpcInvalidRequestException
     */
    public static function fromRaw(RawRpcRequest $rawRpcRequest): self
    {
        $methodParts = explode('.', $rawRpcRequest->getMethod());

        if (!is_array($methodParts)) {
            throw new RpcInvalidRequestException();
        }

        return new self(
            serviceKey: $methodParts[0],
            methodKey: $methodParts[1],
            id: $rawRpcRequest->getId(),
            params: $rawRpcRequest->getParams()
        );
    }

    public function getId(): mixed
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
