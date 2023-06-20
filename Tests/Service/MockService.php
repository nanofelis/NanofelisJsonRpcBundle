<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Service;

use Nanofelis\Bundle\JsonRpcBundle\Attribute\RpcNormalizationContext;
use Nanofelis\Bundle\JsonRpcBundle\Service\AbstractRpcService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\Cache;

class MockService extends AbstractRpcService
{
    public static function getServiceKey(): string
    {
        return 'mockService';
    }

    public function add(int $arg1, int $arg2): int
    {
        return $arg1 + $arg2;
    }

    public function arrayParam(array $a, int $b): array
    {
        $a[] = $b;

        return $a;
    }

    public function requestValueResolver(Request $request): string
    {
        return $request->getMethod();
    }

    /**
     * @RpcNormalizationContext(contexts={"test"})
     */
    public function returnObject(): object
    {
        return new class() {
            public $prop = 'test';
        };
    }

    /**
     * @throws \Exception
     */
    public function willThrowException()
    {
        throw new \Exception('it went wrong', 99);
    }
}
