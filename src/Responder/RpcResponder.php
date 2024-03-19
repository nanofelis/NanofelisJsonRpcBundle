<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Responder;

use Nanofelis\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\JsonRpcBundle\Request\RpcRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

class RpcResponder
{
    public function __invoke(RpcPayload $payload): JsonResponse
    {
        $requests = $payload->getRpcRequests();

        if ($payload->isBatch()) {
            $responseContent = array_map(fn(RpcRequest $rpcRequest) => $rpcRequest->getResponseContent(), $requests);
        } else {
            $responseContent = $requests[0]->getResponseContent();
        }

        return new JsonResponse($responseContent);
    }
}
