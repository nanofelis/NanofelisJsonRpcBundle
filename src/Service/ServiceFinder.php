<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Symfony\Contracts\Cache\CacheInterface;

class ServiceFinder
{
    /**
     * @var array<string,AbstractRpcService>
     */
    private array $rpcServices;

    /**
     * @param \Traversable<string,AbstractRpcService> $rpcServices
     */
    public function __construct(\Traversable $rpcServices)
    {
        $this->rpcServices = iterator_to_array($rpcServices);
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

    /**
     * @return array<string,AbstractRpcService>
     */
    public function getRpcServices(): array
    {
        return $this->rpcServices;
    }
}
