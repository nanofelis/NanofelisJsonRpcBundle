<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Service;

use Nanofelis\JsonRpcBundle\Attribute\RpcIsGranted;
use Nanofelis\JsonRpcBundle\Exception\RpcRoleAccessDeniedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class RpcAuthorizationChecker
{
    public function __construct(
        private ?AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function check(\ReflectionMethod $method): void
    {
        $attributes = $method->getAttributes(RpcIsGranted::class);

        if ([] === $attributes) {
            return;
        }

        if (null === $this->authorizationChecker) {
            throw new \LogicException('RpcIsGranted attribute requires symfony/security-bundle.');
        }

        foreach ($attributes as $attribute) {
            /** @var RpcIsGranted $config */
            $config = $attribute->newInstance();

            if (!$this->authorizationChecker->isGranted($config->attributes)) {
                throw new RpcRoleAccessDeniedException(\sprintf('Access denied. Required role(s): %s', implode(', ', (array) $config->attributes)));
            }
        }
    }
}
