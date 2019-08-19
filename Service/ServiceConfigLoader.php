<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Doctrine\Common\Annotations\Reader;
use Nanofelis\Bundle\JsonRpcBundle\Annotation\RpcNormalizationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class ServiceConfigLoader
{
    /**
     * @var Reader
     */
    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param ServiceDescriptor $descriptor
     */
    public function loadConfig(ServiceDescriptor $descriptor): void
    {
        $this->setNormalizationContexts($descriptor);
        $this->setCacheConfig($descriptor);
    }

    /**
     * @param ServiceDescriptor $methodDescriptor
     */
    private function setNormalizationContexts(ServiceDescriptor $methodDescriptor): void
    {
        $annotation = $this->reader->getMethodAnnotation($methodDescriptor->getMethodReflection(), RpcNormalizationContext::class);

        if (!$annotation instanceof RpcNormalizationContext) {
            return;
        }

        $methodDescriptor->setNormalizationContexts($annotation->getcontexts());
    }

    /**
     * @param ServiceDescriptor $methodDescriptor
     */
    private function setCacheConfig(ServiceDescriptor $methodDescriptor): void
    {
        $annotation = $this->reader->getMethodAnnotation($methodDescriptor->getMethodReflection(), Cache::class);

        if (!$annotation instanceof Cache) {
            return;
        }

        $methodDescriptor->setCacheConfiguration($annotation);
    }
}
