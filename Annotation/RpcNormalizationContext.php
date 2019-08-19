<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RpcNormalizationContext
{
    /**
     * @var array
     */
    private $contexts;

    public function __construct(array $values)
    {
        $this->contexts = $values['value'] ?? [];
    }

    public function getContexts(): array
    {
        return $this->contexts;
    }
}
