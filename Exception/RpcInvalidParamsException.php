<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Exception;

class RpcInvalidParamsException extends AbstractRpcException
{
    public function __construct(string $message = '', \Throwable $previous = null)
    {
        parent::__construct($message, parent::INVALID_PARAMS, $previous);
    }
}
