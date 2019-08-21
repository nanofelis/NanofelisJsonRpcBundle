<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcParseException;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RpcRequestParser
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * RpcRequestParser constructor.
     *
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->serializer = new Serializer([new GetSetMethodNormalizer()]);
    }

    /**
     * @param Request $request
     *
     * @return RpcPayload
     */
    public function parse(Request $request): RpcPayload
    {
        try {
            $data = $this->getContent($request);
        } catch (RpcParseException $e) {
            return $this->getRpcPayloadParseError($e);
        }

        return $this->getRpcPayload($data);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     *
     * @throws RpcParseException
     */
    private function getContent(Request $request)
    {
        return Request::METHOD_GET === $request->getMethod() ? $this->getQueryData($request) : $this->getPostData($request);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     *
     * @throws RpcParseException
     */
    private function getPostData(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if (null === $data) {
            throw new RpcParseException();
        }

        return $data;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function getQueryData(Request $request): array
    {
        return $request->query->all();
    }

    /**
     * @param RpcParseException $e
     *
     * @return RpcPayload
     */
    private function getRpcPayloadParseError(RpcParseException $e): RpcPayload
    {
        $payload = new RpcPayload();
        $rpcRequest = new RpcRequest();
        $rpcRequest->setResponseError(new RpcResponseError($e));
        $payload->addRpcRequest($rpcRequest);

        return $payload;
    }

    /**
     * @param array $data
     *
     * @return RpcPayload
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
     * @param array $data
     *
     * @return RpcRequest
     */
    private function getRpcRequest(array $data): RpcRequest
    {
        try {
            /** @var RpcRequest $rpcRequest */
            $rpcRequest = $this->serializer->denormalize($data, RpcRequest::class);
        } catch (ExceptionInterface $e) {
            throw new \LogicException($e->getMessage());
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
