<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * Class RpcDenormalizationContexts.
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class RpcDenormalizationContexts
{
    /**
     * @var array
     */
    private $contexts;

    /**
     * RpcDenormalizationContexts constructor.
     *
     * @param array $values
     */
    public function __construct(array $values)
    {
        $this->contexts = $values['value'] ?? [];
    }

    /**
     * @return array
     */
    public function getContexts(): array
    {
        return $this->contexts;
    }
}
