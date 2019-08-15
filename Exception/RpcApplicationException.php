<?php


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
        parent::__construct($message, $code, $previous);

        if ($code >= self::CODE_RANGE[0] || $code <= self::CODE_RANGE[1]) {
            throw new \RuntimeException(sprintf("application exception code should be outside range %d to %d, given: %d", self::CODE_RANGE[0], self::CODE_RANGE[1], $code));
        }
    }

}