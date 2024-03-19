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

    public function __construct()
    {
        $this->serializer = new Serializer([new GetSetMethodNormalizer()]);
    }

    public function parse(Request $request): RpcPayload
    {
        try {
            $data = $this->getPostData($request);

            return $this->getRpcPayload($data);
        } catch (AbstractRpcException $e) {
            $payload = new RpcPayload();
            $payload->addRpcResponse(new RpcResponseError($e));

            return $payload;
        }
    }

    /**
     * @return mixed[]
     *
     * @throws RpcParseException|RpcInvalidRequestException
     */
    private function getPostData(Request $request): array
    {
        try {
            $data = json_decode((string) $request->getContent(), true, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RpcParseException(previous: $e);
        }
        if (!is_array($data)) {
            throw new RpcInvalidRequestException();
        }

        return $data;
    }

    /**
     * @param array<string|int,mixed> $data
     *
     * @throws RpcInvalidRequestException
     */
    private function getRpcPayload(mixed $data): RpcPayload
    {
        $payload = new RpcPayload();

        if (!array_is_list($data)) {
            $payload->addRpcRequest($this->getRpcRequest($data));

            return $payload;
        }

        $payload->setIsBatch(true);

        foreach ($data as $subData) {
            $payload->addRpcRequest($this->getRpcRequest((array) $subData));
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
            /** @var RawRpcRequest $rawRpcRequest */
            $rawRpcRequest = $this->serializer->denormalize($data, RawRpcRequest::class);
        } catch (ExceptionInterface) {
            throw new RpcInvalidRequestException();
        }

        return RpcRequest::fromRaw($rawRpcRequest);
    }
}
