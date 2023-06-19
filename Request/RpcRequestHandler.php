<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use Nanofelis\Bundle\JsonRpcBundle\Attribute\RpcNormalizationContext;
use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcApplicationException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcDataExceptionInterface;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidParamsException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceDescriptor;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceFinder;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RpcRequestHandler
{
    public function __construct(private ServiceFinder $serviceFinder, private NormalizerInterface $normalizer, private EventDispatcherInterface $eventDispatcher)
    {
    }

    public function handle(RpcRequest $rpcRequest): void
    {
        if ($rpcRequest->getResponse()) {
            return;
        }

        try {
            $result = $this->execute($rpcRequest);
            $rpcRequest->setResponse(new RpcResponse($result, $rpcRequest->getId()));
        } catch (\Throwable $e) {
            $e = $this->castToRpcException($e);
            $rpcRequest->setResponse(new RpcResponseError($e, $rpcRequest->getId()));
        }
    }

    /**
     * @throws RpcMethodNotFoundException
     * @throws RpcInvalidParamsException
     * @throws ExceptionInterface
     */
    private function execute(RpcRequest $rpcRequest): mixed
    {
        $serviceDescriptor = $this->serviceFinder->find($rpcRequest);

        $this->eventDispatcher->dispatch(new RpcBeforeMethodEvent($rpcRequest, $serviceDescriptor), RpcBeforeMethodEvent::NAME);

        $method = $serviceDescriptor->getMethodName();
        $params = $this->getOrderedParams($serviceDescriptor, $rpcRequest);

//        $this->eventDispatcher->dispatch(new ControllerArgumentsEvent($rpcRequest, $serviceDescriptor), ControllerArgumentsEvent::NAME);

        try {
            $result = $serviceDescriptor->getService()->$method(...$params);
        } catch (\TypeError $e) {
            if ($this->isInvalidParamsException($e, $serviceDescriptor)) {
                throw new RpcInvalidParamsException();
            }
            throw $e;
        }

        return $this->normalizeResult($result, $serviceDescriptor);
    }

    /**
     * @return array<int,mixed>
     */
    private function getOrderedParams(ServiceDescriptor $serviceDescriptor, RpcRequest $rpcRequest): array
    {
        $params = $rpcRequest->getParams() ?: [];
        $orderedParams = [];
        $reflectionParams = $serviceDescriptor->getMethodParameters();

        foreach ($reflectionParams as $reflectionParam) {
            if (\array_key_exists($reflectionParam->getName(), $params)) {
                $orderedParams[] = $params[$reflectionParam->getName()];
            }
        }

        return empty($orderedParams) ? array_values($params) : $orderedParams;
    }

    private function isInvalidParamsException(\TypeError $e, ServiceDescriptor $serviceDescriptor): bool
    {
        $trace = $e->getTrace();

        return $trace[0]['class'] === $serviceDescriptor->getServiceClass() && $trace[0]['function'] === $serviceDescriptor->getMethodName();
    }

    private function castToRpcException(\Throwable $e): AbstractRpcException
    {
        if ($e instanceof AbstractRpcException) {
            return $e;
        }

        $rpcException = new RpcApplicationException($e->getMessage(), $e->getCode());

        if ($e instanceof RpcDataExceptionInterface) {
            $rpcException->setData($e->getData());
        }

        return $rpcException;
    }

    /**
     * @throws ExceptionInterface
     */
    private function normalizeResult(mixed $result, ServiceDescriptor $serviceDescriptor): mixed
    {
        /** @var RpcNormalizationContext|null $normalizationConfig */
        $normalizationConfig = $serviceDescriptor->getMethodAttribute(RpcNormalizationContext::class);

        return $this->normalizer->normalize($result, null, $normalizationConfig ? $normalizationConfig->contexts : []);
    }
}
