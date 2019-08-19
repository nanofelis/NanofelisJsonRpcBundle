<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Exception;

interface RpcDataExceptionInterface
{
    /**
     * @return array|null
     */
    public function getData(): ?array;

    /**
     * @param array|null $data
     */
    public function setData(?array $data): void;
}
