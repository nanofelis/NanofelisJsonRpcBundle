<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Exception;

use Throwable;

class RpcApplicationException extends AbstractRpcException
{
    const CODE_RANGE = [-32099, -32000];

    /**
     * RpcApplicationException constructor.
     *
     * @param string         $message
     * @param int            $code
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        if ($code >= self::CODE_RANGE[0] && $code <= self::CODE_RANGE[1]) {
            throw new \InvalidArgumentException(sprintf('application exception code should be outside range %d to %d, given: %d',
                self::CODE_RANGE[0], self::CODE_RANGE[1], $code));
        }

        parent::__construct($message, $code, $previous);
    }
}
