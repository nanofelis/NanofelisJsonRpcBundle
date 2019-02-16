<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Service;

use Nanofelis\JsonRpcBundle\Annotation\RpcDenormalizationContexts;
use Nanofelis\JsonRpcBundle\Service\ServiceAnnotationReader;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ServiceAnnotationReaderTest extends TestCase
{
    /**
     * @var AnnotationReader|MockObject
     */
    private $reader;

    public function setUp(): void
    {
        $this->reader = $this->createMock(AnnotationReader::class);
    }

    public function testGetDenormalizationContexts()
    {
        $expectedAnnotation = new RpcDenormalizationContexts(['value' => ['context1']]);

        $this->reader
            ->expects($this->once())->method('getMethodAnnotation')
            ->with($this->isInstanceOf(\ReflectionMethod::class), RpcDenormalizationContexts::class)
            ->willReturn($expectedAnnotation);

        $annotationReader = new ServiceAnnotationReader($this->reader);
        $contexts = $annotationReader->getDenormalizationContexts(new MockService(), 'add');

        $this->assertSame($expectedAnnotation->getContexts(), $contexts);
    }

    public function testNoAnnotation()
    {
        $annotationReader = new ServiceAnnotationReader($this->reader);
        $this->reader->method('getMethodAnnotation')->willReturn(null);

        $this->assertSame([], $annotationReader->getDenormalizationContexts(new MockService(), 'add'));
    }

    /**
     * @expectedException \Nanofelis\JsonRpcBundle\Exception\RpcMethodNotFoundException
     */
    public function testMethodNotExist()
    {
        $annotationReader = new ServiceAnnotationReader($this->reader);
        $annotationReader->getDenormalizationContexts(new MockService(), 'unknownMethod');
    }
}
