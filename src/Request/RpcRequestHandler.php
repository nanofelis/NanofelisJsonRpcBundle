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
use Nanofelis\JsonRpcBundle\Response\RpcResponseInterface;
use Nanofelis\JsonRpcBundle\Service\ServiceDescriptor;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
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
        private EventDispatcherInterface $eventDispatcher,
        private HttpKernelInterface $kernel,
    ) {
    }

    /**
     * @throws AbstractRpcException
     * @throws ExceptionInterface
     */
    public function handle(RpcRequest $rpcRequest): RpcResponseInterface
    {
        try {
            return new RpcResponse($this->execute($rpcRequest), $rpcRequest->getId());
        } catch (\Throwable $e) {
            if (!$e instanceof AbstractRpcException) {
                throw $e;
            }

            return new RpcResponseError($e, $rpcRequest->getId());
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
        $service = $serviceDescriptor->getService();
        $method = $serviceDescriptor->getMethodName();
        /** @var callable $callable */
        $callable = [$service, $method];

        $this->eventDispatcher->dispatch(new RpcBeforeMethodEvent($rpcRequest, $serviceDescriptor), RpcBeforeMethodEvent::NAME);

        try {
            $rpcParams = $rpcRequest->getParams() ?? [];
            $request = new Request(
                request: $rpcParams,
                attributes: $rpcParams,
                server: ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'],
            );
            $arguments = $this->argumentResolver->getArguments($request, $callable);
        } catch (\Exception $e) {
            throw new RpcInvalidParamsException(previous: $e);
        }

        $event = new ControllerArgumentsEvent($this->kernel, $callable, $arguments, $request, HttpKernelInterface::MAIN_REQUEST);
        $this->eventDispatcher->dispatch($event, KernelEvents::CONTROLLER_ARGUMENTS);
        $callable = $event->getController();
        $arguments = $event->getArguments();

        try {
            $result = $callable(...$arguments);
        } catch (\TypeError $e) {
            if ($this->isInvalidParamsException($e, $serviceDescriptor)) {
                throw new RpcInvalidParamsException(previous: $e);
            }
            throw $e;
        }

        return $this->normalizeResult($result, $serviceDescriptor);
    }

    private function isInvalidParamsException(\TypeError $e, ServiceDescriptor $serviceDescriptor): bool
    {
        $trace = $e->getTrace();

        // 'class' is absent for plain-function frames, so it must not be accessed blindly
        return ($trace[0]['class'] ?? null) === $serviceDescriptor->getServiceClass() && $trace[0]['function'] === $serviceDescriptor->getMethodName();
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
