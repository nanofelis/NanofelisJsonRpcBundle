<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Controller;

use Nanofelis\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\JsonRpcBundle\Request\RpcRequestParser;
use Nanofelis\JsonRpcBundle\Response\RpcResponseOK;
use Symfony\Component\HttpFoundation\Request;

class RpcController
{
    /**
     * @var RpcRequestParser
     */
    private $parser;

    /**
     * @var RpcRequestHandler
     */
    private $handler;

    public function __construct(RpcRequestParser $parser, RpcRequestHandler $handler)
    {
        $this->parser = $parser;
        $this->handler = $handler;
    }

    /**
     * @param Request $request
     *
     * @return RpcResponseOK
     *
     * @throws \Nanofelis\JsonRpcBundle\Exception\AbstractRpcException
     * @throws \Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function __invoke(Request $request): RpcResponseOK
    {
        $payload = $this->parser->parse($request);
        $data = $this->handler->handle($payload);

        return new RpcResponseOK($data, $payload);
    }
}
