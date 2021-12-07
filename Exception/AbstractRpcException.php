<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Exception;

use Throwable;

abstract class AbstractRpcException extends \Exception implements RpcDataExceptionInterface
{
    public const PARSE = -32700;
    public const INVALID_REQUEST = -32600;
    public const METHOD_NOT_FOUND = -32601;
    public const INVALID_PARAMS = -32602;
    public const INTERNAL = -32603;

    public const MESSAGES = [
        self::PARSE => 'invalid json',
        self::INVALID_REQUEST => 'invalid json-rpc payload',
        self::METHOD_NOT_FOUND => 'method not found',
        self::INVALID_PARAMS => 'invalid params',
        self::INTERNAL => 'internal server error',
    ];

    /**
     * @var array<string,mixed>|null
     */
    private ?array $data = null;

    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        if (empty($message) && isset(self::MESSAGES[$code])) {
            $message = self::MESSAGES[$code];
        }

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return array<string,mixed>|null
     */
    public function getData(): ?array
    {
        return $this->data;
    }

    /**
     * @param array<string,mixed>|null $data
     */
    public function setData(?array $data): void
    {
        $this->data = $data;
    }
}
