<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use Nanofelis\Bundle\JsonRpcBundle\Annotation\RpcNormalizationContext;
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
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RpcRequestHandler
{
    /**
     * @var ServiceFinder
     */
    private $serviceFinder;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        ServiceFinder $serviceFinder,
        NormalizerInterface $normalizer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->serviceFinder = $serviceFinder;
        $this->normalizer = $normalizer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(RpcRequest $rpcRequest): void
    {
        if ($rpcRequest->getResponseError()) {
            return;
        }

        try {
            $result = $this->execute($rpcRequest);
            $rpcRequest->setResponse(new RpcResponse($result, $rpcRequest->getId()));
        } catch (\Throwable $e) {
            $e = $this->castToRpcException($e);
            $rpcRequest->setResponseError(new RpcResponseError($e, $rpcRequest->getId()));
        }
    }

    /**
     * @return array|bool|float|int|string
     *
     * @throws RpcMethodNotFoundException
     * @throws RpcInvalidParamsException
     * @throws ExceptionInterface
     */
    private function execute(RpcRequest $rpcRequest)
    {
        $serviceDescriptor = $this->serviceFinder->find($rpcRequest);

        $this->eventDispatcher->dispatch(new RpcBeforeMethodEvent($rpcRequest, $serviceDescriptor), RpcBeforeMethodEvent::NAME);

        $service = $serviceDescriptor->getService();
        $method = $serviceDescriptor->getMethodName();
        $params = $this->getOrderedParams($serviceDescriptor, $rpcRequest);

        try {
            $result = $service->$method(...$params);
        } catch (\TypeError $e) {
            if ($this->isInvalidParamsException($e, $serviceDescriptor)) {
                throw new RpcInvalidParamsException();
            } else {
                throw $e;
            }
        }

        return $this->normalizeResult($result, $serviceDescriptor);
    }

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
     * @param mixed $result
     *
     * @return mixed
     *
     * @throws ExceptionInterface
     */
    private function normalizeResult($result, ServiceDescriptor $serviceDescriptor)
    {
        /** @var RpcNormalizationContext|null $normalizationConfig */
        $normalizationConfig = $serviceDescriptor->getMethodConfigurations()['_rpc_normalization_context'] ?? null;

        return $this->normalizer->normalize($result, null, $normalizationConfig ? $normalizationConfig->getContexts() : []);
    }
}
