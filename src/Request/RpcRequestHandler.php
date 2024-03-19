<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Nanofelis\JsonRpcBundle\Attribute\RpcNormalizationContext;
use Nanofelis\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcInvalidParamsException;
use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\JsonRpcBundle\Response\RpcResponseError;
use Nanofelis\JsonRpcBundle\Service\ServiceDescriptor;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RpcRequestHandler
{
    public function __construct(
        private ArgumentResolverInterface $argumentResolver,
        private ServiceFinder $serviceFinder,
        private NormalizerInterface $normalizer,
        private EventDispatcherInterface $eventDispatcher
    ) {
    }

    /**
     * @throws AbstractRpcException
     * @throws ExceptionInterface
     */
    public function handle(RpcRequest $rpcRequest): void
    {
        try {
            $result = $this->execute($rpcRequest);
            $rpcRequest->setResponse(new RpcResponse($result, $rpcRequest->getId()));
        } catch (\Throwable $e) {
            if (!$e instanceof AbstractRpcException) {
                throw $e;
            }
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
        [$service, $method] = [$serviceDescriptor->getService(), $serviceDescriptor->getMethodName()];

        $this->eventDispatcher->dispatch(new RpcBeforeMethodEvent($rpcRequest, $serviceDescriptor), RpcBeforeMethodEvent::NAME);

        try {
            $arguments = $this->argumentResolver->getArguments(
                new Request(attributes: $rpcRequest->getParams() ?? []),
                [$service, $method]
            );
        } catch (\Exception $e) {
            throw new RpcInvalidParamsException(previous: $e);
        }

        $result = $service->$method(...$arguments);

        return $this->normalizeResult($result, $serviceDescriptor);
    }

    /**
     * @throws ExceptionInterface
     */
    private function normalizeResult(mixed $result, ServiceDescriptor $serviceDescriptor): mixed
    {
        /** @var \ReflectionAttribute<RpcNormalizationContext>|null $normalizationConfig */
        $normalizationConfig = $serviceDescriptor->getMethodAttribute(RpcNormalizationContext::class);
        $contexts = $normalizationConfig?->getArguments()[0] ?? [];

        return $this->normalizer->normalize($result, null, $contexts);
    }
}
