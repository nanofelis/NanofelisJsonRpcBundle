<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Nanofelis\JsonRpcBundle\Attribute\RpcNormalizationContext;
use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcInvalidParamsException;
use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\JsonRpcBundle\Response\RpcResponseError;
use Nanofelis\JsonRpcBundle\Response\RpcResponseInterface;
use Nanofelis\JsonRpcBundle\Service\ServiceDescriptor;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
        private RequestStack $requestStack,
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

        // outside the try: only argument resolution maps to invalid params, building the request does not
        $request = $this->createArgumentResolutionRequest($rpcRequest);

        try {
            $arguments = $this->argumentResolver->getArguments($request, $callable);
        } catch (\Exception $e) {
            throw new RpcInvalidParamsException(previous: $e);
        }

        // Mirror the incoming request's type rather than asserting one: app listeners on
        // kernel.controller_arguments commonly early-return on !isMainRequest(), and auth is often
        // one of them, so hardcoding SUB_REQUEST disables them silently. getParentRequest() is null
        // for both the main request and an empty stack, which is the answer wanted in either case.
        $requestType = null === $this->requestStack->getParentRequest()
            ? HttpKernelInterface::MAIN_REQUEST
            : HttpKernelInterface::SUB_REQUEST;

        // $request is deliberately NOT pushed onto the RequestStack: that would shadow the real
        // incoming request with a copy whose attributes carry rpc params.
        $event = new ControllerArgumentsEvent($this->kernel, $callable, $arguments, $request, $requestType);
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

    private function createArgumentResolutionRequest(RpcRequest $rpcRequest): Request
    {
        $rpcParams = $rpcRequest->getParams() ?? [];

        // Stand in for the incoming request rather than inventing a bare one: listeners on
        // kernel.controller_arguments — auth among them — read the headers, cookies, session and
        // client ip off it, and duplicate() carries all of that over. Outside an http request
        // (direct invocation) there is nothing to stand in for, so an empty request is the base.
        $request = ($this->requestStack->getCurrentRequest() ?? new Request())->duplicate(request: $rpcParams);
        $request->attributes->add($rpcParams);

        // The resolver reads params out of the request bag, which requires a form content type: the
        // real one is application/json, which would send RequestPayloadValueResolver looking for the
        // payload in the body instead. Set on the headers rather than by handing duplicate() a server
        // array — that re-derives every header from $_SERVER, measured 7x slower on this path, and
        // would let a proxy-supplied HTTP_CONTENT_TYPE win over ours.
        $request->headers->set('CONTENT_TYPE', 'application/x-www-form-urlencoded');

        // The params are the payload, so the json-rpc envelope shared with the incoming request must
        // not stay reachable through getContent(): RequestPayloadValueResolver falls back to it when
        // the request bag is empty, which would turn a nullable #[MapRequestPayload] argument into a
        // 400 where it should resolve to null. Request exposes no setter for the body, hence the
        // closure bound into its scope — the shape Symfony itself uses in InlineFragmentRenderer.
        // initialize() would do it too, at that same 7x cost.
        static $blankBody;
        $blankBody ??= \Closure::bind(static function (Request $request): void {
            $request->content = '';
        }, null, Request::class);
        $blankBody($request);

        return $request;
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
