<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcParseException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest as RpcRequestObject;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\ExceptionInterface as OptionResolverException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class RpcRequestParser
{
    private Serializer $serializer;

    private OptionsResolver $rpcResolver;

    public function __construct()
    {
        $this->serializer = new Serializer([new GetSetMethodNormalizer()]);
        $this->rpcResolver = (new OptionsResolver())
            ->setRequired(['jsonrpc', 'method'])
            ->setDefined(['id'])
            ->setDefined(['params'])
            ->setAllowedValues('jsonrpc', RpcRequestObject::JSON_RPC_VERSION)
            ->setAllowedTypes('method', 'string')
            ->setAllowedValues('method', fn ($value) => 1 === preg_match('/^\w+\.\w+$/', $value))
            ->setAllowedTypes('id', ['string', 'int'])
            ->setAllowedTypes('params', ['array']);
    }

    public function parse(Request $request): RpcPayload
    {
        try {
            $data = $this->getContent($request);

            return $this->getRpcPayload($data);
        } catch (AbstractRpcException $e) {
            return $this->getRpcPayloadError($e);
        }
    }

    /**
     * @throws RpcParseException
     * @throws RpcInvalidRequestException
     */
    private function getContent(Request $request): mixed
    {
        return match ($request->getMethod()) {
            Request::METHOD_POST => $this->getPostData($request),
            Request::METHOD_GET => $this->getQueryData($request),
            default => throw new RpcInvalidRequestException()
        };
    }

    /**
     * @throws RpcParseException
     */
    private function getPostData(Request $request): mixed
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            throw new RpcParseException();
        }

        return $data;
    }

    /**
     * @return array<string,mixed>
     */
    private function getQueryData(Request $request): array
    {
        return $request->query->all();
    }

    private function getRpcPayloadError(AbstractRpcException $e): RpcPayload
    {
        $payload = new RpcPayload();
        $rpcRequest = new RpcRequest();
        $rpcRequest->setResponse(new RpcResponseError($e));
        $payload->addRpcRequest($rpcRequest);

        return $payload;
    }

    /**
     * @param array<string|int,mixed> $data
     *
     * @throws RpcInvalidRequestException
     */
    private function getRpcPayload(array $data): RpcPayload
    {
        $payload = new RpcPayload();

        if ($this->isBatch($data)) {
            $payload->setIsBatch(true);

            foreach ($data as $subData) {
                $payload->addRpcRequest($this->getRpcRequest((array) $subData));
            }
        } else {
            $payload->addRpcRequest($this->getRpcRequest($data));
        }

        return $payload;
    }

    /**
     * @param array<string|int,mixed> $data
     */
    private function isBatch(array $data): bool
    {
        $keys = array_keys($data);

        return !empty($keys) && \is_int($keys[0]);
    }

    /**
     * @param array<string|int,mixed> $data
     *
     * @throws RpcInvalidRequestException
     */
    private function getRpcRequest(array $data): RpcRequest
    {
        try {
            /** @var RpcRequest $rpcRequest */
            $rpcRequest = $this->serializer->denormalize($data, RpcRequest::class);
        } catch (ExceptionInterface) {
            throw new RpcInvalidRequestException();
        }

        try {
            $this->rpcResolver->resolve($data);
        } catch (OptionResolverException $e) {
            $rpcRequest->setResponse(new RpcResponseError(new RpcInvalidRequestException(), $rpcRequest->getId()));

            return $rpcRequest;
        }

        [$serviceKey, $methodKey] = explode('.', $rpcRequest->getMethod());
        $rpcRequest->setServiceKey($serviceKey);
        $rpcRequest->setMethodKey($methodKey);

        return $rpcRequest;
    }
}
