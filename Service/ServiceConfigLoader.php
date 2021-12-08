<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\Reader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationAnnotation;

class ServiceConfigLoader
{
    public function __construct(private Reader $reader)
    {
    }

    public function loadConfig(ServiceDescriptor $descriptor): void
    {
        $annotations = $this->reader->getMethodAnnotations($descriptor->getMethodReflection());

        $this->load($descriptor, $annotations);
    }

    /**
     * @param array<int,Annotation|ConfigurationAnnotation> $annotations
     */
    private function load(ServiceDescriptor $serviceDescriptor, array $annotations): void
    {
        array_walk($annotations, function ($annotation) use ($serviceDescriptor) {
            if ($annotation instanceof ConfigurationAnnotation) {
                $serviceDescriptor->addMethodConfiguration($annotation);
            }
        });
    }
}
