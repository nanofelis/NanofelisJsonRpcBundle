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
     * RPCServiceManager constructor.
     */
    public function __construct(\Traversable $rpcServices)
    {
        $this->rpcServices = $rpcServices;
    }

    /**
     * @param string $serviceKey
     *
     * @return object
     *
     * @throws RpcMethodNotFoundException
     */
    public function find(string $serviceKey): object
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
