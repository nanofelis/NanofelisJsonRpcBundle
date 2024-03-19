<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;

class ServiceFinder
{
    /**
     * @param \Traversable<string,AbstractRpcService> $rpcServices
     */
    public function __construct(private \Traversable $rpcServices)
    {
    }

    /**
     * @throws RpcMethodNotFoundException
     */
    public function find(RpcRequest $rpcRequest): ServiceDescriptor
    {
        $rpcServices = iterator_to_array($this->rpcServices);

        if (!$service = ($rpcServices[$rpcRequest->getServiceKey()] ?? null)) {
            throw new RpcMethodNotFoundException();
        }

        return new ServiceDescriptor($service, $rpcRequest->getMethodKey());
    }

    /**
     * @return \Traversable<string,AbstractRpcService>
     */
    public function getRpcServices(): \Traversable
    {
        return $this->rpcServices;
    }
}
