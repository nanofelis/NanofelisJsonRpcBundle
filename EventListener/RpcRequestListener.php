<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\EventListener;

use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class RpcRequestListener
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
     * @throws RpcInvalidRequestException
     */
    public function convertParams(RpcBeforeMethodEvent $event): void
    {
        $rpcRequest = $event->getRpcRequest();
        $request = $this->requestStack->getCurrentRequest();

        if (empty($rpcRequest->getParams()) || !$request) {
            return;
        }

        $service = $event->getServiceDescriptor();
        $configurations = $this->getParamsConfigurations($service->getMethodReflection(), $request);

        $this->prepareRequestForParamConversion($request, $rpcRequest->getParams());

        $rpcRequest->setParams($this->getConvertedParams($request, $configurations));
    }

    /**
     * @param \ReflectionFunctionAbstract $r
     * @param Request                     $request
     *
     * @return array
     */
    private function getParamsConfigurations(\ReflectionFunctionAbstract $r, Request $request): array
    {
        $configurations = [];

        foreach ($r->getParameters() as $param) {
            if ($param->getClass() && $param->getClass()->isInstance($request)) {
                continue;
            }

            $name = $param->getName();
            $class = $param->getClass();
            $hasType = $param->hasType();

            if ($class || $hasType) {
                $configuration = new ParamConverter([]);
                $configuration->setName($name);

                $configurations[$name] = $configuration;

                if ($class) {
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
     * @param Request $request
     * @param array   $params
     */
    private function prepareRequestForParamConversion(Request $request, array $params): void
    {
        foreach ($params as $key => $val) {
            if (\is_array($val)) {
                return;
            }
            $request->attributes->set($key, $val);
        }
    }

    /**
     * @param Request $request
     * @param array   $configurations
     *
     * @return array
     *
     * @throws RpcInvalidRequestException
     */
    private function getConvertedParams(Request $request, array $configurations): array
    {
        try {
            $this->converterManager->apply($request, $configurations);
        } catch (\Exception $e) {
            throw new RPCInvalidRequestException(sprintf('Param conversion error: %s', $e->getMessage()));
        }

        return array_filter($request->attributes->all(), function ($key) {
            return 0 !== strpos((string) $key, '_');
        }, ARRAY_FILTER_USE_KEY);
    }
}
