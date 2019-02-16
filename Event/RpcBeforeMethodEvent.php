<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Event;

use Nanofelis\JsonRpcBundle\Request\RpcRequestPayload;
use Symfony\Component\EventDispatcher\Event;

class RpcBeforeMethodEvent extends Event
{
    const NAME = 'nanofelis_json_rpc.before_request';

    /**
     * @var RpcRequestPayload
     */
    private $payload;

    /**
     * @var object
     */
    private $service;

    /**
     * @param RpcRequestPayload $payload
     * @param mixed             $service
     */
    public function __construct(RpcRequestPayload $payload, $service)
    {
        $this->payload = $payload;
        $this->service = $service;
    }

    /**
     * @return RpcRequestPayload
     */
    public function getPayload(): RpcRequestPayload
    {
        return $this->payload;
    }

    /**
     * @return mixed
     */
    public function getService()
    {
        return $this->service;
    }
}
