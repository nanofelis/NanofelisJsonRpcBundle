<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Exception;

class RpcApplicationException extends AbstractRpcException
{
    public const CODE_RANGE = [-32099, -32000];

    /**
     * RpcApplicationException constructor.
     */
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if ($code >= self::CODE_RANGE[0] && $code <= self::CODE_RANGE[1]) {
            throw new \InvalidArgumentException(sprintf('application exception code should be outside range %d to %d, given: %d', self::CODE_RANGE[0], self::CODE_RANGE[1], $code));
        }

        parent::__construct($message, $code, $previous);
    }
}
