<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\DependencyInjection\Compiler;

use Nanofelis\JsonRpcBundle\Attribute\JsonRpcService;
use Nanofelis\JsonRpcBundle\Exception\RpcServiceKeyMissingException;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Resolves the #[JsonRpcService] key of every tagged service at container build time and
 * exposes them through a service locator, so that a request only instantiates the single
 * service it names instead of the whole RPC surface.
 */
class RpcServicePass implements CompilerPassInterface
{
    public const TAG = 'nanofelis_json_rpc';

    private const FINDER_ID = 'nanofelis_json_rpc.service.finder';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::FINDER_ID)) {
            return;
        }

        $refMap = [];

        foreach (array_keys($container->findTaggedServiceIds(self::TAG)) as $id) {
            $key = $this->resolveServiceKey($container, $id);

            if (isset($refMap[$key])) {
                throw new InvalidArgumentException(\sprintf('Duplicate json-rpc service key "%s": it is declared by both "%s" and "%s".', $key, $refMap[$key], $id));
            }

            $refMap[$key] = $id;
        }

        $container->getDefinition(self::FINDER_ID)->replaceArgument(
            0,
            ServiceLocatorTagPass::register($container, array_map(
                static fn (string $id) => new Reference($id),
                $refMap,
            )),
        );
    }

    /**
     * @throws RpcServiceKeyMissingException
     */
    private function resolveServiceKey(ContainerBuilder $container, string $id): string
    {
        $definition = $container->findDefinition($id);
        $class = $definition->getClass() ?? $id;

        // getReflectionClass() also registers the file for container cache invalidation
        $reflectionClass = $container->getReflectionClass($class, false);

        if (null === $reflectionClass) {
            throw new InvalidArgumentException(\sprintf('Class "%s" of json-rpc service "%s" does not exist.', $class, $id));
        }

        $attribute = $reflectionClass->getAttributes(JsonRpcService::class)[0] ?? null;

        if (null === $attribute) {
            throw new RpcServiceKeyMissingException($reflectionClass->getName());
        }

        return $attribute->newInstance()->serviceKey;
    }
}
