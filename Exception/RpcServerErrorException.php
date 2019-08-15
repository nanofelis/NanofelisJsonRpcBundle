<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Exception;


use Throwable;

class RpcServerErrorException extends AbstractRpcException
{
    const MIN_RPC_SERVER_ERROR_CODE = 32000;
    const MAX_RPC_SERVER_ERROR_CODE = 32099;

    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        if ($code < self::MIN_RPC_SERVER_ERROR_CODE || $code > self::MAX_RPC_SERVER_ERROR_CODE) {
            throw new \RuntimeException(
                sprintf("invalid rpc error code: $code, allowed range is %d-%d", self::MIN_RPC_SERVER_ERROR_CODE, self::MAX_RPC_SERVER_ERROR_CODE)
            );
        }

        parent::__construct(sprintf("Server error: $message", $code, $previous));
    }
}
