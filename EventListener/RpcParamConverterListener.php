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
    /**
     * @var ParamConverterManager
     */
    private $converterManager;

    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * RpcRequestListener constructor.
     *
     * @param RequestStack          $requestStack
     * @param ParamConverterManager $converterManager
     */
    public function __construct(RequestStack $requestStack, ParamConverterManager $converterManager)
    {
        $this->converterManager = $converterManager;
        $this->requestStack = $requestStack;
    }

    /**
     * @param RpcBeforeMethodEvent $event
     *
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
     * @param ServiceDescriptor $serviceDescriptor
     * @param Request           $request
     *
     * @return array
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
            if ($param->getClass() && $param->getClass()->isInstance($request)) {
                continue;
            }

            $name = $param->getName();
            $class = $param->getClass();
            $hasType = method_exists('ReflectionParameter', 'getType') && $param->hasType();

            if ($class || $hasType) {
                if (!isset($configurations[$name])) {
                    $configuration = new ParamConverter([]);
                    $configuration->setName($name);

                    $configurations[$name] = $configuration;
                }

                if ($class && null === $configurations[$name]->getClass()) {
                    $configurations[$name]->setClass($class->getName());
                }
            }

            if (isset($configurations[$name])) {
                $configurations[$name]->setIsOptional($param->isOptional() || $param->isDefaultValueAvailable() || $hasType && $param->getType()->allowsNull());
            }
        }

        return $configurations;
    }

    /**
     * Hydrate request attributes bag with parameters from the rpc request. Array are excluded as attributes must be
     * scalars.
     *
     * @param Request    $request
     * @param RpcRequest $rpcRequest
     */
    private function prepareRequestForParamConversion(RpcRequest $rpcRequest, Request $request): void
    {
        foreach ($rpcRequest->getParams() as $key => $val) {
            if (\is_array($val)) {
                continue;
            }
            $request->attributes->set((string)$key, $val);
        }
    }

    /**
     * @param Request $request
     * @param array   $configurations
     *
     * @throws RpcInvalidParamsException
     */
    private function applyConfigurations(Request $request, array $configurations)
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
            $params[$key] = $request->attributes->get((string)$key, $params[$key]);
        }

        $rpcRequest->setParams($params);
    }
}
