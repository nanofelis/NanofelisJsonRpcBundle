<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Service;

use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;

class ServiceDescriptor
{
    private \ReflectionMethod $methodReflection;

    /**
     * ServiceDescriptor constructor.
     *
     * @throws RpcMethodNotFoundException
     */
    public function __construct(private object $service, string $method)
    {
        // Magic methods are never part of the RPC surface. Exposing them would notably let a
        // caller re-run __construct on the shared service instance and overwrite its dependencies.
        if (str_starts_with($method, '__')) {
            throw new RpcMethodNotFoundException();
        }

        try {
            $this->methodReflection = new \ReflectionMethod($service::class, $method);
        } catch (\ReflectionException) {
            throw new RpcMethodNotFoundException();
        }

        // Reflection also resolves non-public methods; calling one would raise a raw \Error and
        // surface as a 500. Report them as unknown so that visibility is not disclosed either.
        if (!$this->methodReflection->isPublic() || $this->methodReflection->isAbstract()) {
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
     * @template T of object
     *
     * @param class-string<T> $class
     *
     * @return \ReflectionAttribute<T>|null
     */
    public function getMethodAttribute(string $class): ?\ReflectionAttribute
    {
        return $this->methodReflection->getAttributes($class)[0] ?? null;
    }
}
