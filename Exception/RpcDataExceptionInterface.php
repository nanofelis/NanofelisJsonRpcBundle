<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Exception;

interface RpcDataExceptionInterface
{
    /**
     * @return array<string|int,mixed>
     */
    public function getData(): ?array;

    /**
     * @param array<string|int,mixed> $data
     */
    public function setData(?array $data): void;
}
