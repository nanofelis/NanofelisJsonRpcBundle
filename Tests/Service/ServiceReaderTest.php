<?php
declare(strict_types=1);

namespace App\Tests\RpcService;

use App\Rpc\Annotation\RpcNormalizationContext;
use App\Rpc\Exception\RpcMethodNotFoundException;
use App\Rpc\Service\ServiceReader;
use App\Tests\unit\Rpc\Service\MockService;
use Doctrine\Common\Annotations\AnnotationReader;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class ServiceReaderTest extends TestCase
{
    /**
     * @var AnnotationReader|MockObject
     */
    private $reader;

    protected function setUp(): void
    {
        $this->reader = $this->createMock(AnnotationReader::class);
    }

    public function testGetNormalizationContexts()
    {
        $expectedAnnotation = new RpcNormalizationContext(['value' => ['context1']]);

        $this->reader
            ->expects($this->once())->method('getMethodAnnotation')
            ->with($this->isInstanceOf(\ReflectionMethod::class), RpcNormalizationContext::class)
            ->willReturn($expectedAnnotation);

        $contexts = (new ServiceReader($this->reader))->getNormalizationContexts(new MockService(), 'add');

        $this->assertSame($expectedAnnotation->getContexts(), $contexts);
    }

    public function testGetCacheConfig()
    {
        $expectedAnnotation = new Cache([]);

        $this->reader
            ->expects($this->once())->method('getMethodAnnotation')
            ->with($this->isInstanceOf(\ReflectionMethod::class), Cache::class)
            ->willReturn($expectedAnnotation);

        $cache = (new ServiceReader($this->reader))->getCacheConfig(new MockService(), 'add');

        $this->assertSame($expectedAnnotation, $cache);
    }

    public function testGetParameters()
    {
        $annotationReader = new ServiceReader($this->reader);

        $parameters = $annotationReader->getParameters(new MockService(), 'add');

        $this->assertSame('arg1', $parameters[0]->getName());
    }

    public function testMethodNotExist()
    {
        $this->expectException(RpcMethodNotFoundException::class);

        $annotationReader = new ServiceReader($this->reader);

        $annotationReader->getNormalizationContexts(new MockService(), 'unknownMethod');
    }
}