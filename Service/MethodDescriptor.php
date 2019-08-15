<?php

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class MethodDescriptor
{
    /**
     * @var object
     */
    private $service;

    /**
     * @var \ReflectionMethod[]
     */
    private $reflection = [];

    /**
     * @var array
     */
    private $normalizationContexts = [];

    /**
     * @var null|Cache
     */
    private $cacheConfiguration;

    /**
     * MethodDescriptor constructor.
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
            $this->reflection = new \ReflectionMethod(get_class($service), $method);
        } catch (\ReflectionException $e) {
            throw new RpcMethodNotFoundException();
        }
    }

    public function getReflection()
    {
        return $this->reflection;
    }

    public function getName(): string
    {
        return $this->reflection->getName();
    }

    /**
     * @return object
     */
    public function getService(): object
    {
        return $this->service;
    }

    /**
     * @return \ReflectionParameter[]
     */
    public function getParameters(): array
    {
        return $this->reflection->getParameters();
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