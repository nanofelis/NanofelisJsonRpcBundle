<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Request;

use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponse;
use Nanofelis\Bundle\JsonRpcBundle\Response\RpcResponseError;

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
     * @var string|null Format serviceKey.methodKey
     */
    private $method;

    /**
     * @var string|null
     */
    private $serviceKey;

    /**
     * @var string|null
     */
    private $methodKey;

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
     * @return string|null
     */
    public function getServiceKey(): ?string
    {
        return $this->serviceKey;
    }

    /**
     * @param string|null $serviceKey
     */
    public function setServiceKey(?string $serviceKey): void
    {
        $this->serviceKey = $serviceKey;
    }

    /**
     * @return string|null
     */
    public function getMethodKey(): ?string
    {
        return $this->methodKey;
    }

    /**
     * @param string|null $methodKey
     */
    public function setMethodKey(?string $methodKey): void
    {
        $this->methodKey = $methodKey;
    }

    /**
     * @return array|null
     */
    public function getParams(): ?array
    {
        return $this->params;
    }

    /**
     * @param array|null $params
     */
    public function setParams(?array $params): void
    {
        $this->params = $params;
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
