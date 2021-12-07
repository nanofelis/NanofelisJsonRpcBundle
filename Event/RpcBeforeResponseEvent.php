<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Event;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Symfony\Contracts\EventDispatcher\Event;

class RpcBeforeResponseEvent extends Event
{
    public const NAME = 'nanofelis_json_rpc.before_response';

    /**
     * @var RpcRequest
     */
    private $rpcRequest;

    /**
     * RpcBeforeResponseEvent constructor.
     */
    public function __construct(RpcRequest $rpcRequest)
    {
        $this->rpcRequest = $rpcRequest;
    }

    public function getRpcRequest(): RpcRequest
    {
        return $this->rpcRequest;
    }
}
