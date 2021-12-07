<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Exception;

interface RpcDataExceptionInterface
{
    public function getData(): ?array;

    public function setData(?array $data): void;
}
