<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Response;

interface RpcResponseInterface
{
    public function getContent(): array;
}
