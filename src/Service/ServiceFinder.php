<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Psr\Container\ContainerInterface;

class ServiceFinder
{
    /**
     * @var array<string,ServiceDescriptor>
     */
    private array $descriptors = [];

    /**
     * $rpcServices is a service locator keyed by #[JsonRpcService] key, built by RpcServicePass,
     * so that only the service a request actually names is ever instantiated.
     */
    public function __construct(private ContainerInterface $rpcServices)
    {
    }

    /**
     * @throws RpcMethodNotFoundException
     */
    public function find(RpcRequest $rpcRequest): ServiceDescriptor
    {
        $serviceKey = $rpcRequest->getServiceKey();
        $methodKey = $rpcRequest->getMethodKey();

        // descriptors are immutable, so they can be shared across the requests of a batch
        return $this->descriptors[$serviceKey.'.'.$methodKey] ??= new ServiceDescriptor(
            $this->getService($serviceKey),
            $methodKey,
        );
    }

    /**
     * @throws RpcMethodNotFoundException
     */
    private function getService(string $serviceKey): object
    {
        if (!$this->rpcServices->has($serviceKey)) {
            throw new RpcMethodNotFoundException();
        }

        // PSR-11 get() is untyped, but a service locator only ever holds services
        /** @var object $service */
        $service = $this->rpcServices->get($serviceKey);

        return $service;
    }
}
