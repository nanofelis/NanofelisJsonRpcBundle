<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Service;

use Nanofelis\Bundle\JsonRpcBundle\Attribute\RpcNormalizationContext;
use Nanofelis\Bundle\JsonRpcBundle\Service\AbstractRpcService;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Attribute\MapDateTime;

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

    public function testArrayParam(array $b, int $a): array
    {
        $b[] = $a;

        return $b;
    }

    #[Cache(maxage: 3600, public: true)]
    public function dateParamConverter(#[MapDateTime(format: "y-m-d")] \DateTime $date): string
    {
        return $date->format('d-m-Y');
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
