<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
final class RpcIsGranted
{
    /**
     * @param string|list<string> $attributes
     */
    public function __construct(
        public string|array $attributes,
    ) {
    }
}
