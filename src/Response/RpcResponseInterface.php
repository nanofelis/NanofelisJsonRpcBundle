<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Response;

interface RpcResponseInterface
{
    /**
     * @return array<string,mixed>
     */
    public function getContent(): array;
}
