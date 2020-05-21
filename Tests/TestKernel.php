<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests;

use Nanofelis\Bundle\JsonRpcBundle\NanofelisJsonRpcBundle;
use Nanofelis\Bundle\JsonRpcBundle\Tests\Service\MockService;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Exception\LoaderLoadException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class TestKernel extends Kernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    /**
     * Returns an array of bundles to register.
     *
     * @return iterable|BundleInterface[] An iterable of bundle instances
     */
    public function registerBundles()
    {
        return [
            new FrameworkBundle(),
            new SensioFrameworkExtraBundle(),
            new NanofelisJsonRpcBundle(),
        ];
    }

    /**
     * @param RouteCollectionBuilder $routes
     *
     * @throws LoaderLoadException
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import(__DIR__.'/../Resources/config/routing/routing.xml');
    }

    /**
     * @param ContainerBuilder $c
     * @param LoaderInterface  $loader
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->loadFromExtension('framework', [
            'test' => true,
            'serializer' => [
                'enabled' => true,
            ],
        ]);
        $c->loadFromExtension('sensio_framework_extra', [
            'router' => [
                'annotations' => false,
            ],
        ]);
        $c->setParameter('kernel.secret', 'fake');
    }

    /**
     * @param ContainerBuilder $c
     */
    public function process(ContainerBuilder $c)
    {
        $c->register(MockService::class, MockService::class)
            ->addTag('nanofelis_json_rpc')
            ->setPublic(true);
    }
}
