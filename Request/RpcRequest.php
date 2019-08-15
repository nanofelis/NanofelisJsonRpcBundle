<?php

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseInterface;
use Symfony\Component\HttpFoundation\Request;

class RpcRequest
{
    /**
     * @var string|null
     */
    private $jsonrpc;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $serviceId;

    /**
     * @var string|null
     */
    private $method;

    /**
     * @var array|null
     */
    private $params;

    /**
     * @var RpcResponse|null
     */
    private $response;

    /**
     * @var RpcResponseError|null
     */
    private $responseError;

    /**
     * RpcRequest constructor.
     *
     * @param string|null $jsonrpc
     * @param string|null $method
     * @param string|null $id
     * @param array|null  $params
     */
    public function __construct(?string $jsonrpc = null, ?string $method = null, ?string $id = null, ?array $params = null)
    {
        $this->jsonrpc = $jsonrpc;
        $this->id = $id;
        $this->method = $method;
        $this->params = $params;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getMethod(): ?string
    {
        return $this->method;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @return RpcResponse|null
     */
    public function getResponse(): ?RpcResponse
    {
        return $this->response;
    }
    /**
     * @param RpcResponse|null $response
     */
    public function setResponse(?RpcResponse $response): void
    {
        $this->response = $response;
    }

    /**
     * @return RpcResponseError|null
     */
    public function getResponseError(): ?RpcResponseError
    {
        return $this->responseError;
    }

    /**
     * @param RpcResponseError|null $responseError
     */
    public function setResponseError(?RpcResponseError $responseError): void
    {
        $this->responseError = $responseError;
    }

    public function getResponseContent(): ?array
    {
        return $this->getResponse() ? $this->getResponse()->getContent() : $this->getResponseError()->getContent();
    }
}
