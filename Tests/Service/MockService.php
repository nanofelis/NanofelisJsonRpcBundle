<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Service;

use Nanofelis\Bundle\JsonRpcBundle\Annotation\RpcNormalizationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;

class MockService
{
    public function add(int $arg1, int $arg2): int
    {
        return $arg1 + $arg2;
    }

    public function testArrayParam(array $b, int $a): array
    {
        $b[] = $a;

        return $b;
    }

    public function getDateIso(\DateTime $date): string
    {
        return $date->format(\DateTime::ISO8601);
    }

    /**
     * @Cache(public=true, maxage=3600)
     * @RpcNormalizationContext("test")
     */
    public function annotatedMethod()
    {
        $object = new \stdClass();
        $object->param = 'test';

        return $object;
    }

    /**
     * @throws \Exception
     */
    public function willThrowException()
    {
        throw new \Exception('it went wrong');
    }
}
