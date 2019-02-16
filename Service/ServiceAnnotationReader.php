<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Service;

use Nanofelis\JsonRpcBundle\Annotation\RpcDenormalizationContexts;
use Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException;
use Doctrine\Common\Annotations\AnnotationReader;

class ServiceAnnotationReader
{
    /**
     * @var AnnotationReader
     */
    private $reader;

    /**
     * ServiceAnnotationReader constructor.
     *
     * @param AnnotationReader $reader
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param mixed  $service
     * @param string $method
     *
     * @return array
     *
     * @throws RpcMethodNotFoundException
     */
    public function getDenormalizationContexts($service, string $method): array
    {
        try {
            $reflMethod = new \ReflectionMethod(\get_class($service), $method);
        } catch (\ReflectionException $e) {
            throw new RpcMethodNotFoundException();
        }

        $annotation = $this->reader->getMethodAnnotation($reflMethod, RpcDenormalizationContexts::class);

        if (!$annotation instanceof RpcDenormalizationContexts) {
            return [];
        }

        return $annotation->getContexts();
    }
}
