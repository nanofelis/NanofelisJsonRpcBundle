<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Doctrine\Common\Annotations\Annotation;
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
        $annotations = $this->reader->getMethodAnnotations($descriptor->getMethodReflection());

        $this->load($descriptor, $annotations);
    }

    /**
     * @param ServiceDescriptor $serviceDescriptor
     * @param Annotation[]      $annotations
     */
    private function load(ServiceDescriptor $serviceDescriptor, array $annotations): void
    {
        foreach ($annotations as $annotation) {
            switch (true) {
                case $annotation instanceof RpcNormalizationContext:
                    $serviceDescriptor->setNormalizationContexts($annotation->getContexts());
                    break;
                case $annotation instanceof Cache:
                    $serviceDescriptor->setCacheConfiguration($annotation);
                    break;
                default:
            }
        }
    }
}
