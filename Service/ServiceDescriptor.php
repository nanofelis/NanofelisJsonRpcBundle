<?php

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class ServiceDescriptor
{
    /**
     * @var object
     */
    private $service;

    /**
     * @var \ReflectionMethod[]
     */
    private $methodReflection = [];

    /**
     * @var array
     */
    private $normalizationContexts = [];

    /**
     * @var Cache|null
     */
    private $cacheConfiguration;

    /**
     * ServiceDescriptor constructor.
     *
     * @param object $service
     * @param string $method
     *
     * @throws RpcMethodNotFoundException
     */
    public function __construct(object $service, string $method)
    {
        $this->service = $service;

        try {
            $this->methodReflection = new \ReflectionMethod(get_class($service), $method);
        } catch (\ReflectionException $e) {
            throw new RpcMethodNotFoundException();
        }
    }

    public function getMethodReflection()
    {
        return $this->methodReflection;
    }

    public function getMethodName(): string
    {
        return $this->methodReflection->getName();
    }

    /**
     * @return object
     */
    public function getService(): object
    {
        return $this->service;
    }

    /**
     * @return string
     */
    public function getServiceClass(): string
    {
        return get_class($this->service);
    }

    /**
     * @return \ReflectionParameter[]
     */
    public function getMethodParameters(): array
    {
        return $this->methodReflection->getParameters();
    }

    /**
     * @return array
     */
    public function getNormalizationContexts(): array
    {
        return $this->normalizationContexts;
    }

    /**
     * @param array $normalizationContexts
     */
    public function setNormalizationContexts(array $normalizationContexts): void
    {
        $this->normalizationContexts = $normalizationContexts;
    }

    /**
     * @return Cache|null
     */
    public function getCacheConfiguration(): ?Cache
    {
        return $this->cacheConfiguration;
    }

    /**
     * @param Cache|null $cacheConfiguration
     */
    public function setCacheConfiguration(?Cache $cacheConfiguration): void
    {
        $this->cacheConfiguration = $cacheConfiguration;
    }
}
