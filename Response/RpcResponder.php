<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Response;

use Nanofelis\Bundle\JsonRpcBundle\Event\RpcBeforeResponseEvent;
use Nanofelis\Bundle\JsonRpcBundle\Request\RpcPayload;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class RpcResponder
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * RpcResponder constructor.
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function __invoke(RpcPayload $payload): JsonResponse
    {
        $responseContent = null;

        if ($payload->isBatch()) {
            foreach ($payload->getRpcRequests() as $rpcRequest) {
                $this->eventDispatcher->dispatch(new RpcBeforeResponseEvent($rpcRequest), RpcBeforeResponseEvent::NAME);
                $responseContent[] = $rpcRequest->getResponseContent();
            }
        } else {
            $rpcRequest = $payload->getRpcRequests()[0];
            $this->eventDispatcher->dispatch(new RpcBeforeResponseEvent($rpcRequest), RpcBeforeResponseEvent::NAME);
            $responseContent = $rpcRequest->getResponseContent();
        }

        return new JsonResponse($responseContent);
    }
}
