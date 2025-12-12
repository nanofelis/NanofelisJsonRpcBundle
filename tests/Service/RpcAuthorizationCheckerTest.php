<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Service;

use Nanofelis\JsonRpcBundle\Attribute\RpcIsGranted;
use Nanofelis\JsonRpcBundle\Exception\RpcRoleAccessDeniedException;
use Nanofelis\JsonRpcBundle\Service\RpcAuthorizationChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final class RpcAuthorizationCheckerTest extends TestCase
{
    public function testDoesNothingWhenNoAttribute(): void
    {
        $checker = new RpcAuthorizationChecker(null);

        $method = new \ReflectionMethod(OpenService::class, 'open');

        $checker->check($method);

        $this->assertTrue(true);
    }

    public function testThrowsLogicExceptionWhenSecurityMissing(): void
    {
        $checker = new RpcAuthorizationChecker(null);

        $method = new \ReflectionMethod(SecuredService::class, 'secured');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('RpcIsGranted attribute requires symfony/security-bundle.');

        $checker->check($method);
    }

    public function testThrowsAccessDeniedWhenNotGranted(): void
    {
        $security = $this->createMock(AuthorizationCheckerInterface::class);
        $security
            ->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(false);

        $checker = new RpcAuthorizationChecker($security);

        $method = new \ReflectionMethod(SecuredService::class, 'secured');

        $this->expectException(RpcRoleAccessDeniedException::class);

        $checker->check($method);
    }

    public function testPassesWhenGranted(): void
    {
        $security = $this->createMock(AuthorizationCheckerInterface::class);
        $security
            ->expects($this->once())
            ->method('isGranted')
            ->with('ROLE_ADMIN')
            ->willReturn(true);

        $checker = new RpcAuthorizationChecker($security);

        $method = new \ReflectionMethod(SecuredService::class, 'secured');

        $checker->check($method);

        $this->assertTrue(true);
    }
}

final class OpenService
{
    public function open(): void
    {
    }
}

final class SecuredService
{
    #[RpcIsGranted('ROLE_ADMIN')]
    public function secured(): void
    {
    }
}
