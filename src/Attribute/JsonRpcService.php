<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
class JsonRpcService
{
    public function __construct(
        public string $serviceKey,
    ) {
    }
}
