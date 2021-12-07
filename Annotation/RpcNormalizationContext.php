<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Annotation;

use Doctrine\Common\Annotations\Annotation\Target;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class RpcNormalizationContext extends ConfigurationAnnotation
{
    /**
     * @var array
     */
    private $contexts;

    public function getContexts(): array
    {
        return $this->contexts;
    }

    public function setContexts(array $context): void
    {
        $this->contexts = $context;
    }

    /**
     * Returns the alias name for an annotated configuration.
     */
    public function getAliasName(): string
    {
        return 'rpc_normalization_context';
    }

    /**
     * Returns whether multiple annotations of this type are allowed.
     */
    public function allowArray(): bool
    {
        return false;
    }
}
