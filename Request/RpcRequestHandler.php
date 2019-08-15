<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use App\Rpc\Exception\RpcDataExceptionInterface;
use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcApplicationException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInternalException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidParamsException;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;
use Nanofelis\Bundle\JsonRpcBundle\Service\MethodDescriptor;
use Nanofelis\Bundle\JsonRpcBundle\Service\MethodReader;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceFinder;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

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
     * @var MethodReader
     */
    private $methodReader;

    public function __construct(
        ServiceFinder $serviceFinder,
        MethodReader $methodReader,
        NormalizerInterface $normalizer
    )
    {
        $this->serviceFinder = $serviceFinder;
        $this->normalizer = $normalizer;
        $this->methodReader = $methodReader;
    }

    /**
     * @param RpcRequest $rpcRequest
     *
     * @throws AbstractRpcException
     * @throws ExceptionInterface
     */
    public function handle(RpcRequest $rpcRequest): void
    {
        try {
            $this->execute($rpcRequest);
        } catch (\Throwable $e) {
            $e = $this->castToRpcException($e);
            $rpcRequest->setResponseError(new RpcResponseError($e, $rpcRequest->getId()));
        }
    }

    private function execute(RpcRequest $rpcRequest): void
    {
        [$serviceKey, $method] = explode('.', $rpcRequest->getMethod());

        $service = $this->serviceFinder->find($serviceKey);
        $methodDescriptor = $this->methodReader->read($service, $method);
        $method = $methodDescriptor->getName();
        $params = $this->getOrderedParams($methodDescriptor, $rpcRequest);

        $data = $service->$method(...$params);

        if ($cache = $methodDescriptor->getCacheConfiguration()) {
            $rpcRequest->getRequest()->attributes->set('_cache', $cache);
        }

        $data = $this->normalizer->normalize($data, null, $methodDescriptor->getNormalizationContexts());

        $rpcRequest->setResponse(new RpcResponse($data, $rpcRequest->getId()));
    }

    /**
     * @param MethodDescriptor  $methodDescriptor
     * @param RpcRpcRequest $payload
     *
     * @return array
     */
    private function getOrderedParams(MethodDescriptor $methodDescriptor, RpcRequest $payload): array
    {
        $params = $payload->getParams() ?: [];
        $orderedParams = [];
        $reflectionParams = $methodDescriptor->getParameters();

        foreach ($reflectionParams as $reflectionParam) {
            if (array_key_exists($reflectionParam->getName(), $params)) {
                $orderedParams[] = $params[$reflectionParam->getName()];
            }
        }

        return empty($orderedParams) ? $params : $orderedParams;
    }

    /**
     * @param \Throwable $e
     *
     * @return AbstractRpcException
     */
    private function castToRpcException(\Throwable $e): AbstractRpcException
    {
        switch ($e) {
            case $e instanceof AbstractRpcException:
                return $e;
                break;
            case $e instanceof \TypeError:
                $rpcError = new RpcInvalidParamsException();
                break;
            default:
                $rpcError = new RpcApplicationException($e->getMessage(), $e->getCode());
        }

        if ($e instanceof RpcDataExceptionInterface) {
            $rpcError->setData($e->getData());
        }

        return $rpcError;
    }
}
