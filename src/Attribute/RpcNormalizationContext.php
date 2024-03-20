<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Attribute;

#[\Attribute(\Attribute::TARGET_METHOD)]
class RpcNormalizationContext
{
    /**
     * @param array<string,mixed> $contexts
     */
    public function __construct(
        public array $contexts = [],
    ) {
    }
}
