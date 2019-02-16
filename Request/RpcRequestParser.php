<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Nanofelis\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\JsonRpcBundle\Exception\RpcParseException;
use Nanofelis\JsonRpcBundle\Validator\Constraints\RequestPayload;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
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
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
        $this->serializer = new Serializer([new GetSetMethodNormalizer()], [new JsonEncoder()]);
    }

    /**
     * @param Request $request
     *
     * @return RpcRequestPayload
     *
     * @throws ExceptionInterface
     * @throws RpcInvalidRequestException
     * @throws RpcParseException
     */
    public function parse(Request $request): RpcRequestPayload
    {
        $data = $this->decode($request->getContent());
        $this->validate($data);
        $payload = $this->getPayload($data);
        $payload->setRequest($request);

        return $payload;
    }

    /**
     * @param string $content
     *
     * @return array
     *
     * @throws RpcParseException
     */
    private function decode(string $content): array
    {
        $data = $this->serializer->decode($content, 'json');

        if (null === $data) {
            throw new RpcParseException();
        }

        return $data;
    }

    /**
     * @param array $data
     *
     * @throws RpcInvalidRequestException
     */
    private function validate(array $data): void
    {
        $violations = $this->validator->validate($data, new RequestPayload());

        if ($violations->count() > 0) {
            throw new RpcInvalidRequestException();
        }
    }

    /**
     * @param array $data
     *
     * @return RpcRequestPayload
     *
     * @throws ExceptionInterface
     */
    private function getPayload(array $data): RpcRequestPayload
    {
        /** @var RpcRequestPayload $payload */
        $payload = $this->serializer->denormalize($data, RpcRequestPayload::class);

        [$serviceId, $method] = explode('.', $payload->getMethod());

        $payload->setServiceId($serviceId);
        $payload->setMethod($method);

        return $payload;
    }
}
