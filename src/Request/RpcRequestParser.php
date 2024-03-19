<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Nanofelis\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\JsonRpcBundle\Exception\RpcParseException;
use Nanofelis\JsonRpcBundle\Response\RpcResponseError;
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
            ->setAllowedValues('jsonrpc', RpcRequest::JSON_RPC_VERSION)
            ->setAllowedTypes('method', 'string')
            ->setAllowedValues('method', fn ($value) => 1 === preg_match('/^\w+\.\w+$/', $value))
            ->setAllowedTypes('id', ['string', 'int'])
            ->setAllowedTypes('params', ['array']);
    }

    public function parse(Request $request): RpcPayload
    {
        try {
            $data = $this->getPostData($request);

            return $this->getRpcPayload($data);
        } catch (AbstractRpcException $e) {
            $payload = new RpcPayload();
            $payload->addRpcRequest(new RpcRequest(response: new RpcResponseError($e)));

            return $payload;
        }
    }

    /**
     * @throws RpcParseException
     */
    private function getPostData(Request $request): mixed
    {
        try {
            $data = json_decode((string) $request->getContent(), true, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RpcParseException(previous: $e);
        }

        return $data;
    }

    /**
     * @param array<string|int,mixed> $data
     *
     * @throws RpcInvalidRequestException
     */
    private function getRpcPayload(array $data): RpcPayload
    {
        $payload = new RpcPayload();

        if (array_is_list($data)) {
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
        } catch (OptionResolverException) {
            $rpcRequest->setResponse(new RpcResponseError(new RpcInvalidRequestException(), $rpcRequest->getId()));

            return $rpcRequest;
        }

        [$serviceKey, $methodKey] = explode('.', $rpcRequest->getMethod());
        $rpcRequest->setServiceKey($serviceKey);
        $rpcRequest->setMethodKey($methodKey);

        return $rpcRequest;
    }
}
