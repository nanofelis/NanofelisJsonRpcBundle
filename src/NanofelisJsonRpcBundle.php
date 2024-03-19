<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class NanofelisJsonRpcBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
