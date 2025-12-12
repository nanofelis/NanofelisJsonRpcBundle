<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Exception;

class RpcServiceKeyMissingException extends AbstractRpcException
{
    public function __construct(string $serviceClass, ?\Throwable $previous = null)
    {
        $message = \sprintf('The service "%s" must define a key via #[JsonRpcService]', $serviceClass);

        parent::__construct($message, self::INTERNAL, $previous);
    }
}
