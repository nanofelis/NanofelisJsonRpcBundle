<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

class ServiceDescriptor
{
    private \ReflectionMethod $methodReflection;

    /**
     * @var ConfigurationAnnotation[]|ConfigurationAnnotation[][]
     */
    private array $methodConfigurations = [];

    /**
     * ServiceDescriptor constructor.
     *
     * @throws RpcMethodNotFoundException
     */
    public function __construct(private object $service, string $method)
    {
        try {
            $this->methodReflection = new \ReflectionMethod($service::class, $method);
        } catch (\ReflectionException) {
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

    public function getService(): object
    {
        return $this->service;
    }

    public function getServiceClass(): string
    {
        return $this->service::class;
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

    public function addMethodConfiguration(ConfigurationAnnotation $configuration): void
    {
        if ($configuration->allowArray()) {
            $this->methodConfigurations['_'.$configuration->getAliasName()][] = $configuration;
        } else {
            $this->methodConfigurations['_'.$configuration->getAliasName()] = $configuration;
        }
    }
}
