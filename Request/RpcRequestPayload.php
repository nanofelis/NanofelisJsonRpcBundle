<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Request;

use Symfony\Component\HttpFoundation\Request;

class RpcRequestPayload
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string
     */
    private $serviceId;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array|null
     */
    private $params;

    /**
     * @var Request
     */
    private $request;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getServiceId(): string
    {
        return $this->serviceId;
    }

    /**
     * @return string
     */
    public function getMethod(): string
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
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @param string $serviceId
     */
    public function setServiceId(string $serviceId): void
    {
        $this->serviceId = $serviceId;
    }

    /**
     * @param string $method
     */
    public function setMethod(string $method): void
    {
        $this->method = $method;
    }

    /**
     * @param array $params
     */
    public function setParams(?array $params): void
    {
        $this->params = $params;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest(Request $request): void
    {
        $this->request = $request;
    }
}
