<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcParseException;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RpcRequestParser
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * RpcRequestParser constructor.
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->serializer = new Serializer([new GetSetMethodNormalizer()]);
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
     * @return mixed
     *
     * @throws RpcParseException
     * @throws RpcInvalidRequestException
     */
    private function getContent(Request $request)
    {
        if (Request::METHOD_POST === $request->getMethod()) {
            return $this->getPostData($request);
        }

        if (Request::METHOD_GET === $request->getMethod()) {
            return $this->getQueryData($request);
        }

        throw new RpcInvalidRequestException();
    }

    /**
     * @return mixed
     *
     * @throws RpcParseException
     */
    private function getPostData(Request $request)
    {
        $data = json_decode((string) $request->getContent(), true);

        if (null === $data) {
            throw new RpcParseException();
        }

        return $data;
    }

    private function getQueryData(Request $request): array
    {
        return $request->query->all();
    }

    private function getRpcPayloadError(AbstractRpcException $e): RpcPayload
    {
        $payload = new RpcPayload();
        $rpcRequest = new RpcRequest();
        $rpcRequest->setResponseError(new RpcResponseError($e));
        $payload->addRpcRequest($rpcRequest);

        return $payload;
    }

    /**
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

    private function isBatch(array $data): bool
    {
        $keys = array_keys($data);

        return !empty($keys) && \is_int($keys[0]);
    }

    /**
     * @throws RpcInvalidRequestException
     */
    private function getRpcRequest(array $data): RpcRequest
    {
        try {
            /** @var RpcRequest $rpcRequest */
            $rpcRequest = $this->serializer->denormalize($data, RpcRequest::class);
        } catch (ExceptionInterface|\TypeError $e) {
            throw new RpcInvalidRequestException();
        }

        if (\count($this->validator->validate($rpcRequest)) > 0) {
            $rpcRequest->setResponseError(new RpcResponseError(new RpcInvalidRequestException(), $rpcRequest->getId()));

            return $rpcRequest;
        }

        [$serviceKey, $methodKey] = explode('.', $rpcRequest->getMethod());
        $rpcRequest->setServiceKey($serviceKey);
        $rpcRequest->setMethodKey($methodKey);

        return $rpcRequest;
    }
}
