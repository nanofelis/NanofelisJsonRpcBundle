<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Exception;

use Nanofelis\JsonRpcBundle\Request\RpcRequestPayload;
use Throwable;

abstract class AbstractRpcException extends \Exception
{
    const PARSE = -32700;
    const INVALID_REQUEST = -32600;
    const METHOD_NOT_FOUND = -32601;
    const INVALID_PARAMS = -32602;
    const INTERNAL = -32603;

    const MESSAGES = [
        self::PARSE => 'Parse error ',
        self::INVALID_REQUEST => 'Invalid Request',
        self::METHOD_NOT_FOUND => 'Method notfound',
        self::INVALID_PARAMS => 'Invalid params',
        self::INTERNAL => 'internal error',
    ];

    /**
     * @var RpcRequestPayload|null
     */
    private $payload;

    /**
     * @var array
     */
    private $data;

    /**
     * @return array|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * AbstractRpcException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (empty($message)) {
            $message = self::MESSAGES[$code];
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return RpcRequestPayload|null
     */
    public function getPayload(): ?RpcRequestPayload
    {
        return $this->payload;
    }

    /**
     * @param RpcRequestPayload|null $payload
     */
    public function setPayload(?RpcRequestPayload $payload): void
    {
        $this->payload = $payload;
    }
}
