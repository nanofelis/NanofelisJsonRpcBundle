<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Controller;

use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeMethodEvent;
use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeResponseEvent;
use Nanofelis\Bundle\JsonRpcBundle\Exception\AbstractRpcException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcInvalidRequestException;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcParseException;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestParser;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequestPayload;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\Bundle\JsonRpcBundle\Validator\Constraints\RpcRequest;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RpcController
{
    /**
     * @var RpcRequestParser
     */
    private $parser;

    /**
     * @var RpcRequestHandler
     */
    private $handler;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(
        RpcRequestParser $parser,
        RpcRequestHandler $handler,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->handler = $handler;
        $this->parser = $parser;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws AbstractRpcException
     * @throws RpcInvalidRequestException
     * @throws RpcParseException
     * @throws ExceptionInterface
     */
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $this->parser->parse($request);
        $this->execute($payload);

        return $this->buildReponse($payload);
    }

    private function execute(RpcRequestPayload $payload)
    {
        foreach ($payload->getValidRpcRequests() as $rpcRequest) {
//            $this->eventDispatcher->dispatch(new RpcBeforeMethodEvent($rpcRequest));
            $this->handler->handle($rpcRequest);
        }
    }

    private function buildReponse(RpcRequestPayload $payload): JsonResponse
    {
        $responseContent = null;

        if ($payload->isBatch()) {
            foreach ($payload->getRpcRequests() as $rpcRequest) {
                $responseContent[] = $rpcRequest->getResponseContent();
            }
        } else {
            $rpcRequest = array_pop($payload->getRpcRequests()[0]);
            $responseContent = $rpcRequest->getResponseContent();
        }

        //  $this->eventDispatcher->dispatch(new RpcBeforeResponseEvent($rpcRequest));

        return new JsonResponse($responseContent);
    }
}
