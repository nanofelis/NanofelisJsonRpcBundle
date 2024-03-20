<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Exception;

class RpcMethodNotFoundException extends AbstractRpcException
{
    public function __construct(string $message = '', ?\Throwable $previous = null)
    {
        parent::__construct($message, parent::METHOD_NOT_FOUND, $previous);
    }
}
