<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceConfigLoader;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceDescriptor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceConfigLoaderTest extends TestCase
{
    /**
     * @var AnnotationReader|MockObject
     */
    private $annotationReader;

    /**
     * @var ServiceConfigLoader
     */
    private $configLoader;

    protected function setUp(): void
    {
        $this->annotationReader = new AnnotationReader(new DocParser());
        $this->configLoader = new ServiceConfigLoader($this->annotationReader);
    }

    public function testRead()
    {
        $serviceDescriptor = new ServiceDescriptor(new MockService(), 'annotatedMethod');
        $this->configLoader->loadConfig($serviceDescriptor);

        $this->assertSame(['test'], $serviceDescriptor->getNormalizationContexts());
        $this->assertSame(3600, $serviceDescriptor->getCacheConfiguration()->getMaxAge());
    }
}
