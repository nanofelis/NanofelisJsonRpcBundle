<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Event;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Symfony\Component\EventDispatcher\Event;

class RpcBeforeResponseEvent extends Event
{
    const NAME = 'nanofelis_json_rpc.before_response';

    /**
     * @var RpcRequest
     */
    private $rpcRequest;

    /**
     * RpcBeforeResponseEvent constructor.
     *
     * @param RpcRequest $rpcRequest
     */
    public function __construct(RpcRequest $rpcRequest)
    {
        $this->rpcRequest = $rpcRequest;
    }

    /**
     * @return RpcRequest
     */
    public function getRpcRequest(): RpcRequest
    {
        return $this->rpcRequest;
    }
}
