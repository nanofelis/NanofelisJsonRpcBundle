<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;

class ServiceFinder
{
    /**
     * @var \Traversable
     */
    private $rpcServices;

    /**
     * @var ServiceConfigLoader
     */
    private $serviceConfigLoader;

    /**
     * ServiceFinder constructor.
     */
    public function __construct(\Traversable $rpcServices, ServiceConfigLoader $serviceConfigLoader)
    {
        $this->rpcServices = $rpcServices;
        $this->serviceConfigLoader = $serviceConfigLoader;
    }

    /**
     * @throws RpcMethodNotFoundException
     */
    public function find(RpcRequest $rpcRequest): ServiceDescriptor
    {
        $service = $this->search($rpcRequest->getServiceKey());
        $descriptor = new ServiceDescriptor($service, $rpcRequest->getMethodKey());

        $this->serviceConfigLoader->loadConfig($descriptor);

        return $descriptor;
    }

    /**
     * @throws RpcMethodNotFoundException
     */
    private function search(string $serviceKey): object
    {
        foreach ($this->rpcServices as $service) {
            $class = explode('\\', \get_class($service));

            if (end($class) === ucfirst($serviceKey)) {
                return $service;
            }
        }

        throw new RpcMethodNotFoundException();
    }
}
