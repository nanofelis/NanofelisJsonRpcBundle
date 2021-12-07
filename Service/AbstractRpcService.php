<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

abstract class AbstractRpcService
{
    abstract public static function getServiceKey(): string;
}
