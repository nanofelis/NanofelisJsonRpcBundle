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
     * @var RpcRequest
     */
    private $rpcRequest;

    /**
     * @var ServiceDescriptor
     */
    private $serviceDescriptor;

    /**
     * RpcBeforeMethodEvent constructor.
     */
    public function __construct(RpcRequest $rpcRequest, ServiceDescriptor $serviceDescriptor)
    {
        $this->rpcRequest = $rpcRequest;
        $this->serviceDescriptor = $serviceDescriptor;
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
