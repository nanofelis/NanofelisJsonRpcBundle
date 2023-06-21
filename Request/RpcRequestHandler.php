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
use PHPStan\BetterReflection\Reflection\ReflectionAttribute;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
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
    )
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

        [$service, $method] = [$serviceDescriptor->getService(), $serviceDescriptor->getMethodName()];
        $request = Request::create('/');
        $request->attributes->replace($rpcRequest->getParams() ?? []);

        try {
            $arguments = $this->argumentResolver->getArguments($request, [$service, $method]);
        } catch (\Exception) {
            throw new RpcInvalidParamsException();
        }

        try {
            $result = $serviceDescriptor->getService()->$method(...$arguments);
        } catch (\TypeError $e) {
            if ($this->isInvalidParamsException($e, $serviceDescriptor)) {
                throw new RpcInvalidParamsException();
            }
            throw $e;
        }

        return $this->normalizeResult($result, $serviceDescriptor);
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
        /** @var ReflectionAttribute|null $normalizationConfig */
        $normalizationConfig = $serviceDescriptor->getMethodAttribute(RpcNormalizationContext::class);
        $contexts = $normalizationConfig?->getArguments()[0] ?? [];

        return $this->normalizer->normalize($result, null, $contexts);
    }
}
