<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcParseException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInternalException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
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
     * @return RpcRequestPayload
     *
     * @throws RpcInvalidRequestException
     * @throws RpcParseException
     */
    public function parse(Request $request): RpcRequestPayload
    {
        $data = $this->_parse($request);

        return $this->getRpcPayload($data, $request);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     *
     * @throws RpcParseException
     */
    private function _parse(Request $request)
    {
        return Request::METHOD_GET === $request->getMethod() ? $this->parseGetRequest($request) : $this->parsePostRequest($request);
    }

    /**
     * @param Request $request
     *
     * @return mixed
     *
     * @throws RpcParseException
     */
    private function parsePostRequest(Request $request)
    {
        $data = json_decode($request->getContent(),true);

        if (is_null($data)) {
            throw new RpcParseException();
        }

        return $data;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    private function parseGetRequest(Request $request): array
    {
        return $request->query->all();
    }

    /**
     * @param array   $data
     *
     * @return RpcRequest|RpcRequest[]
     *
     * @throws ExceptionInterface
     */
    private function getRpcPayload(array $data)
    {
        $payload = new RpcRequestPayload();
        $keys = array_keys($data);

        if (empty($keys)) {
            return $payload;
        }

        if (is_int($keys[0])) {
            $payload->setIsBatch(true);
            foreach ($data as $subData) {
                $payload->addRpcRequest($this->getRpcRequest((array) $subData));
            }
        } else {
            $payload->addRpcRequest($this->getRpcRequest((array) $data));
        }

        return $payload;
    }

    /**
     * @param array $data
     *
     * @return RpcRequest
     *
     * @throws RpcInternalException
     */
    private function getRpcRequest(array $data): RpcRequest
    {
        try {
            /** @var RpcRpcRequest $rpcRequest */
            $rpcRequest = $this->serializer->denormalize($data, RpcRequest::class);
        } catch (ExceptionInterface $e) {
            throw new RpcInternalException($e->getMessage());
        }

        if (count($violations = $this->validator->validate($rpcRequest)) > 0) {
            $rpcRequest->setResponseError(new RpcResponseError(new RpcInvalidRequestException()));

            return $rpcRequest;
        }

        return $rpcRequest;
    }
}
