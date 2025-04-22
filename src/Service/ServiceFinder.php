<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Service;

use Nanofelis\JsonRpcBundle\Attribute\JsonRpcService;
use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Exception\RpcServiceKeyMissingException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;

class ServiceFinder
{
    /**
     * @var array<string, object>
     */
    private array $rpcServices = [];

    /**
     * @param \Traversable<string, object> $rpcServices
     */
    public function __construct(\Traversable $rpcServices)
    {
        foreach ($rpcServices as $service) {
            $key = $this->resolveServiceKey($service);
            if (null === $key) {
                throw new RpcServiceKeyMissingException($service::class);
            }

            $this->rpcServices[$key] = $service;
        }
    }

    private function resolveServiceKey(object $service): ?string
    {
        $reflectionClass = new \ReflectionClass($service);

        $attribute = $reflectionClass->getAttributes(JsonRpcService::class)[0] ?? null;
        if ($attribute) {
            /** @var JsonRpcService $instance */
            $instance = $attribute->newInstance();

            return $instance->serviceKey;
        }

        return null;
    }

    /**
     * @throws RpcMethodNotFoundException
     */
    public function find(RpcRequest $rpcRequest): ServiceDescriptor
    {
        if (!$service = ($this->rpcServices[$rpcRequest->getServiceKey()] ?? null)) {
            throw new RpcMethodNotFoundException();
        }

        return new ServiceDescriptor($service, $rpcRequest->getMethodKey());
    }
}
