<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Exception;

use Nanofelis\Bundle\JsonRpcBundle\Request\RpcRequest;
use Throwable;

abstract class AbstractRpcException extends \Exception
{
    const PARSE = -32700;
    const INVALID_REQUEST = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS = -32602;
    const INTERNAL = -32603;

    const MESSAGES = [
        self::PARSE => 'invalid json',
        self::INVALID_REQUEST => 'invalid json-rpc payload',
        self::METHOD_NOT_FOUND => 'method not found',
        self::INVALID_PARAMS => 'invalid params',
        self::INTERNAL => 'internal server error',
    ];

    /**
     * @var RpcRpcRequest|null
     */
    private $payload;

    /**
     * @var array
     */
    private $data;

    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            $message = self::MESSAGES[$code];
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
    }

    public function getPayload(): ?RpcRequest
    {
        return $this->payload;
    }

    /**
     * @param RpcRpcRequest|null $payload
     */
    public function setPayload(?RpcRequest $payload): void
    {
        $this->payload = $payload;
    }
}
