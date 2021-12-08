<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Event;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Symfony\Contracts\EventDispatcher\Event;

class RpcBeforeResponseEvent extends Event
{
    public const NAME = 'nanofelis_json_rpc.before_response';

    /**
     * RpcBeforeResponseEvent constructor.
     */
    public function __construct(private RpcRequest $rpcRequest)
    {
    }

    public function getRpcRequest(): RpcRequest
    {
        return $this->rpcRequest;
    }
}
