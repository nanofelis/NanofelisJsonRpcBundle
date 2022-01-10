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
        /**
         * @param array<int,Annotation|ConfigurationAnnotation> $annotations
         */
        $annotations = $this->reader->getMethodAnnotations($descriptor->getMethodReflection());

        foreach ($annotations as $annotation) {
            if ($annotation instanceof ConfigurationAnnotation) {
                $descriptor->addMethodConfiguration($annotation);
            }
        }
    }
}
