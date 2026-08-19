<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle;

use Nanofelis\JsonRpcBundle\DependencyInjection\Compiler\RpcServicePass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class NanofelisJsonRpcBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // TYPE_OPTIMIZE is where Symfony itself runs ServiceLocatorTagPass and
        // ResolveTaggedIteratorArgumentPass, so it is the right stage for building a locator out
        // of a tag. It also has to be this late: a kernel implementing CompilerPassInterface is
        // registered at BEFORE_OPTIMIZATION/-10000, i.e. last in that stage, so services such a
        // kernel registers are not yet visible to a bundle pass running there. Moving this pass
        // earlier yields an empty locator and -32601 for every call.
        $container->addCompilerPass(new RpcServicePass(), PassConfig::TYPE_OPTIMIZE);
    }
}
