<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Service;

use Doctrine\Common\Annotations\Reader;
use Nanofelis\Bundle\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Nanofelis\Bundle\JsonRpcBundle\Annotation\RpcNormalizationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class MethodReader
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
     * @param object $service
     * @param string $method
     *
     * @return MethodDescriptor
     *
     * @throws RpcMethodNotFoundException
     */
    public function read(object $service, string $method): MethodDescriptor
    {
        $methodDescriptor = new MethodDescriptor($service, $method);

        $this->setNormalizationContexts($methodDescriptor);
        $this->setCacheConfig($methodDescriptor);

        return $methodDescriptor;
    }

    /**
     * @param MethodDescriptor $methodDescriptor
     *
     * @return array
     */
    private function setNormalizationContexts(MethodDescriptor $methodDescriptor): void
    {
        $annotation = $this->reader->getMethodAnnotation($methodDescriptor->getReflection(), RpcNormalizationContext::class);

        if (!$annotation instanceof RpcNormalizationContext) {
            return;
        }

        $methodDescriptor->setNormalizationContexts($annotation->getcontexts());
    }

    /**
     * @param MethodDescriptor $methodDescriptor
     */
    private function setCacheConfig(MethodDescriptor $methodDescriptor): void
    {
        $annotation = $this->reader->getMethodAnnotation($methodDescriptor->getReflection(), Cache::class);

        if (!$annotation instanceof Cache) {
            return;
        }

        $methodDescriptor->setCacheConfiguration($annotation);
    }
}
