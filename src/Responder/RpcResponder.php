<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Responder;

use Nanofelis\JsonRpcBundle\Request\RpcPayload;
use Nanofelis\JsonRpcBundle\Response\RpcResponseInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class RpcResponder
{
    public function __invoke(RpcPayload $payload): JsonResponse
    {
        $responses = $payload->getRpcResponses();

        if ($payload->isBatch()) {
            $responseContent = array_map(fn (RpcResponseInterface $rpcResponse) => $rpcResponse->getContent(), $responses);
        } else {
            $responseContent = $responses[0]->getContent();
        }

        return new JsonResponse($responseContent);
    }
}
