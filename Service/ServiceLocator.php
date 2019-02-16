<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Request\RpcRequestPayload;

class ServiceLocator
{
    /**
     * @var \Traversable
     */
    private $RPCServices;

    /**
     * RPCServiceManager constructor.
     */
    public function __construct(\Traversable $RPCServices)
    {
        $this->RPCServices = $RPCServices;
    }

    /**
     * @param RpcRequestPayload $payload
     *
     * @return mixed
     *
     * @throws RpcMethodNotFoundException
     */
    public function find(RpcRequestPayload $payload)
    {
        $serviceKey = $payload->getServiceId();
        $method = $payload->getMethod();
        $service = $this->getService($serviceKey);

        if (!method_exists($service, $method)) {
            throw new RpcMethodNotFoundException();
        }

        return $service;
    }

    /**
     * @param string $serviceKey
     *
     * @return mixed
     *
     * @throws RpcMethodNotFoundException
     */
    private function getService(string $serviceKey)
    {
        foreach ($this->RPCServices as $service) {
            $class = explode('\\', \get_class($service));

            if (end($class) === sprintf('%sService', ucfirst($serviceKey))) {
                return $service;
            }
        }

        throw new RpcMethodNotFoundException();
    }
}
