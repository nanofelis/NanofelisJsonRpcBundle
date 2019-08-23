<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Event;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceDescriptor;
use Symfony\Component\EventDispatcher\Event;

class RpcBeforeMethodEvent extends Event
{
    const NAME = 'nanofelis_json_rpc.before_method';

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
     *
     * @param RpcRequest        $rpcRequest
     * @param ServiceDescriptor $serviceDescriptor
     */
    public function __construct(RpcRequest $rpcRequest, ServiceDescriptor $serviceDescriptor)
    {
        $this->rpcRequest = $rpcRequest;
        $this->serviceDescriptor = $serviceDescriptor;
    }

    /**
     * @return RpcRequest
     */
    public function getRpcRequest(): RpcRequest
    {
        return $this->rpcRequest;
    }

    /**
     * @return ServiceDescriptor
     */
    public function getServiceDescriptor(): ServiceDescriptor
    {
        return $this->serviceDescriptor;
    }
}
