<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Event;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceDescriptor;
use Symfony\Contracts\EventDispatcher\Event;

class RpcBeforeMethodEvent extends Event
{
    public const NAME = 'nanofelis_json_rpc.before_method';

    /**
     * RpcBeforeMethodEvent constructor.
     */
    public function __construct(private RpcRequest $rpcRequest, private ServiceDescriptor $serviceDescriptor)
    {
    }

    public function getRpcRequest(): RpcRequest
    {
        return $this->rpcRequest;
    }

    public function getServiceDescriptor(): ServiceDescriptor
    {
        return $this->serviceDescriptor;
    }
}
