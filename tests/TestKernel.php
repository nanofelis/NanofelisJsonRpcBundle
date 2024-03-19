<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests;

use Nanofelis\JsonRpcBundle\NanofelisJsonRpcBundle;
use Nanofelis\JsonRpcBundle\Tests\Service\MockService;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;
use Symfony\Component\Routing\Loader\PhpFileLoader as RoutingPhpFileLoader;
use Symfony\Component\Routing\RouteCollection;

class TestKernel extends Kernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    /**
     * Returns an array of bundles to register.
     *
     * @return iterable|BundleInterface[] An iterable of bundle instances
     */
    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new TwigBundle(),
            new NanofelisJsonRpcBundle(),
        ];
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__.'/../config/routing/routing.xml');
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->loadFromExtension('framework', [
            'test' => true,
            'serializer' => [
                'enabled' => true,
            ],
        ]);
        $container->setParameter('kernel.secret', 'fake');
    }

    public function process(ContainerBuilder $container): void
    {
        $container->register(MockService::class, MockService::class)
            ->addTag('nanofelis_json_rpc')
            ->setPublic(true);
    }

    /**
     * @internal
     */
    public function loadRoutes(LoaderInterface $loader): RouteCollection
    {
        $file = (new \ReflectionObject($this))->getFileName();
        /* @var RoutingPhpFileLoader $kernelLoader */
        $kernelLoader = $loader->getResolver()->resolve($file, 'php');
        $kernelLoader->setCurrentDir(\dirname($file));
        $collection = new RouteCollection();

        $configureRoutes = new \ReflectionMethod($this, 'configureRoutes');
        $configureRoutes->getClosure($this)(new RoutingConfigurator($collection, $kernelLoader, $file, $file, $this->getEnvironment()));

        foreach ($collection as $route) {
            $controller = $route->getDefault('_controller');

            if (\is_array($controller) && [0, 1] === array_keys($controller) && $this === $controller[0]) {
                $route->setDefault('_controller', ['kernel', $controller[1]]);
            }
        }

        return $collection;
    }
}
