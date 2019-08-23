<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceConfigLoader;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceDescriptor;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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

    public function testLoadConfig()
    {
        $serviceDescriptor = new ServiceDescriptor(new MockService(), 'dateParamConverter');
        $this->configLoader->loadConfig($serviceDescriptor);

        $this->assertInstanceOf(ParamConverter::class, $serviceDescriptor->getMethodConfigurations()['_converters'][0]);
        $this->assertInstanceOf(Cache::class, $serviceDescriptor->getMethodConfigurations()['_cache']);
    }
}
