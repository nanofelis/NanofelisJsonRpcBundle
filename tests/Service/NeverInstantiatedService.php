<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Service;

use Nanofelis\JsonRpcBundle\Attribute\JsonRpcService;

/**
 * Guards the laziness of service resolution: calling a method on another rpc service must not
 * instantiate this one. If someone reintroduces eager iteration over the tagged services, the
 * flag below flips and RpcTest::testUnusedServicesAreNotInstantiated fails.
 */
#[JsonRpcService('neverInstantiatedService')]
class NeverInstantiatedService
{
    public static bool $instantiated = false;

    public function __construct()
    {
        self::$instantiated = true;
    }

    public function noop(): bool
    {
        return true;
    }
}
