<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Exception;

class RpcRoleAccessDeniedException extends AbstractRpcException
{
    public function __construct(string $message = 'Access denied', ?\Throwable $previous = null)
    {
        parent::__construct($message, parent::INTERNAL, $previous);
    }
}
