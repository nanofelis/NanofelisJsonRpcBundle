<?php

declare(strict_types=1);

namespace App\Rpc\Exception;

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