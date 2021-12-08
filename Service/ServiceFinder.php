<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;

class ServiceFinder
{
    /**
     * @param \Traversable<string,AbstractRpcService> $rpcServices
     */
    public function __construct(private \Traversable $rpcServices, private ServiceConfigLoader $serviceConfigLoader)
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

        $descriptor = new ServiceDescriptor($service, $rpcRequest->getMethodKey());
        $this->serviceConfigLoader->loadConfig($descriptor);

        return $descriptor;
    }
}
