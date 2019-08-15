<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\EventListener;

use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInternalException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

class RpcRequestListener implements EventSubscriberInterface
{
    /**
     * @var ParamConverterManager
     */
    private $converterManager;

    /**
     * RpcRequestListener constructor.
     *
     * @param ParamConverterManager $converterManager
     */
    public function __construct(ParamConverterManager $converterManager)
    {
        $this->converterManager = $converterManager;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            RpcBeforeMethodEvent::NAME => 'onRPCBeforeMethodEvent',
        ];
    }

    /**
     * @param RpcBeforeMethodEvent $event
     *
     * @throws RpcInternalException
     * @throws RpcInvalidRequestException
     */
    public function onRPCBeforeMethodEvent(RpcBeforeMethodEvent $event)
    {
        if (empty($event->getRpcRequest()->getParams())) {
            return;
        }

        $payload = $event->getPayload();
        $service = $event->getService();
        $method = $payload->getMethod();
        $request = $payload->getRequest();
        $reflectionMethod = $this->getReflectionMethod($service, $method);
        $configurations = $this->getParamsConfigurations($reflectionMethod, $request);

        $this->prepareRequestForParamConversion($request, $payload->getParams());

        $payload->setParams($this->getConvertedParams($request, $configurations));
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
            throw new RPCInvalidRequestException(sprintf('Param conversion error'));
        }

        return array_filter($request->attributes->all(), function ($key) {
            return 0 !== strpos((string) $key, '_');
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param mixed  $service
     * @param string $method
     *
     * @return \ReflectionMethod
     *
     * @throws RpcInternalException
     */
    private function getReflectionMethod($service, string $method): \ReflectionMethod
    {
        $class = \get_class($service);
        try {
            return new \ReflectionMethod($class, $method);
        } catch (\ReflectionException $e) {
            throw new RPCInternalException('Param conversion error');
        }
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
}
