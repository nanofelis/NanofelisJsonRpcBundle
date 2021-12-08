<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\EventListener;

use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidParamsException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceDescriptor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RpcParamConverterListener
{
    public function __construct(private RequestStack $requestStack, private ParamConverterManager $converterManager)
    {
    }

    /**
     * @throws RpcInvalidParamsException
     */
    public function convertParams(RpcBeforeMethodEvent $event): void
    {
        $rpcRequest = $event->getRpcRequest();
        $request = $this->requestStack->getCurrentRequest();

        if (empty($rpcRequest->getParams()) || !$request) {
            return;
        }

        $serviceDescriptor = $event->getServiceDescriptor();
        $configurations = $this->getParamsAutoConfigurations($serviceDescriptor, $request);

        $this->prepareRequestForParamConversion($rpcRequest, $request);
        $this->applyConfigurations($request, $configurations);
        $this->mergeParams($rpcRequest, $request);
    }

    /**
     * @return array<string,ParamConverter|null>
     */
    private function getParamsAutoConfigurations(ServiceDescriptor $serviceDescriptor, Request $request): array
    {
        $configurations = [];
        $reflection = $serviceDescriptor->getMethodReflection();
        /** @var ParamConverter[] $currentConfigurations */
        $currentConfigurations = $serviceDescriptor->getMethodConfigurations()['_converters'] ?? [];

        foreach ($currentConfigurations as $configuration) {
            $configurations[$configuration->getName()] = $configuration;
        }

        foreach ($reflection->getParameters() as $param) {
            $type = $param->getType();
            $class = $this->getParamClassByType($type);
            if (null !== $class && $request instanceof $class) {
                continue;
            }

            $name = $param->getName();

            if ($type) {
                if (!isset($configurations[$name])) {
                    $configuration = new ParamConverter([]);
                    $configuration->setName($name);

                    $configurations[$name] = $configuration;
                }

                /* @phpstan-ignore-next-line */
                if (null !== $class && null === $configurations[$name]->getClass()) {
                    $configurations[$name]->setClass($class);
                }
            }

            if (isset($configurations[$name])) {
                $configurations[$name]->setIsOptional($param->isOptional() || $param->isDefaultValueAvailable() || ($type && $type->allowsNull()));
            }
        }

        return $configurations;
    }

    private function getParamClassByType(?\ReflectionType $type): ?string
    {
        if (null === $type) {
            return null;
        }

        foreach ($type instanceof \ReflectionUnionType ? $type->getTypes() : [$type] as $type) {
            if (!$type->isBuiltin()) {
                return $type->getName();
            }
        }

        return null;
    }

    /**
     * Hydrate request attributes bag with parameters from the rpc request. Array are excluded as attributes must be
     * scalars.
     */
    private function prepareRequestForParamConversion(RpcRequest $rpcRequest, Request $request): void
    {
        foreach ($rpcRequest->getParams() as $key => $val) {
            if (\is_array($val)) {
                continue;
            }
            $request->attributes->set((string) $key, $val);
        }
    }

    /**
     * @param array<string,ParamConverter|null> $configurations
     *
     * @throws RpcInvalidParamsException
     */
    private function applyConfigurations(Request $request, array $configurations): void
    {
        try {
            $this->converterManager->apply($request, $configurations);
        } catch (\Exception $e) {
            throw new RpcInvalidParamsException(sprintf('Param conversion error: %s', $e->getMessage()));
        }
    }

    private function mergeParams(RpcRequest $rpcRequest, Request $request): void
    {
        $params = $rpcRequest->getParams();

        foreach ($params as $key => $val) {
            $params[$key] = $request->attributes->get((string) $key, $val);
        }

        $rpcRequest->setParams($params);
    }
}
