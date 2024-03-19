<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Exception;

class RpcInternalException extends AbstractRpcException
{
    public function __construct(string $message = '', \Throwable $previous = null)
    {
        parent::__construct($message, parent::INTERNAL, $previous);
    }
}
