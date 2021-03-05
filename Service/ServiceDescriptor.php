<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

class ServiceDescriptor
{
    /**
     * @var object
     */
    private $service;

    /**
     * @var \ReflectionMethod
     */
    private $methodReflection;

    /**
     * @var ConfigurationAnnotation[]|ConfigurationAnnotation[][]
     */
    private $methodConfigurations = [];

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
            $this->methodReflection = new \ReflectionMethod(\get_class($service), $method);
        } catch (\ReflectionException $e) {
            throw new RpcMethodNotFoundException();
        }
    }

    public function getMethodReflection(): \ReflectionMethod
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
        return \get_class($this->service);
    }

    /**
     * @return \ReflectionParameter[]
     */
    public function getMethodParameters(): array
    {
        return $this->methodReflection->getParameters();
    }

    /**
     * @return ConfigurationAnnotation[]|ConfigurationAnnotation[][]
     */
    public function getMethodConfigurations(): array
    {
        return $this->methodConfigurations;
    }

    /**
     * @param ConfigurationAnnotation $configuration
     */
    public function addMethodConfiguration(ConfigurationAnnotation $configuration): void
    {
        if ($configuration->allowArray()) {
            $this->methodConfigurations['_'.$configuration->getAliasName()][] = $configuration;
        } else {
            $this->methodConfigurations['_'.$configuration->getAliasName()] = $configuration;
        }
    }
}
