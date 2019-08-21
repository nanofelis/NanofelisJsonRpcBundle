<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Response;

use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeResponseEvent;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcPayload;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class RpcResponder
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * RpcResponder constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param RpcPayload $payload
     *
     * @return JsonResponse
     */
    public function __invoke(RpcPayload $payload): JsonResponse
    {
        $responseContent = null;

        if ($payload->isBatch()) {
            foreach ($payload->getRpcRequests() as $rpcRequest) {
                $this->eventDispatcher->dispatch(RpcBeforeResponseEvent::NAME, new RpcBeforeResponseEvent($rpcRequest));
                $responseContent[] = $rpcRequest->getResponseContent();
            }
        } else {
            $rpcRequest = $payload->getRpcRequests()[0];
            $this->eventDispatcher->dispatch(RpcBeforeResponseEvent::NAME, new RpcBeforeResponseEvent($rpcRequest));
            $responseContent = $rpcRequest->getResponseContent();
        }

        return new JsonResponse($responseContent);
    }
}
