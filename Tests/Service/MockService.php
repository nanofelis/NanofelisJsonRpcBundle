<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Service;

use Nanofelis\Bundle\JsonRpcBundle\Annotation\RpcNormalizationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

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

    /**
     * @Cache(public=true, maxage=3600)
     * @ParamConverter("date", options={"format": "Y-m-d"})
     */
    public function dateParamConverter(\DateTime $date): string
    {
        return $date->format('d-m-Y');
    }

    /**
     * @RpcNormalizationContext(contexts={"test"})
     */
    public function returnObject()
    {
        $object = new class() {
            public $prop = 'test';
        };

        return $object;
    }

    /**
     * @throws \Exception
     */
    public function willThrowException()
    {
        throw new \Exception('it went wrong', 99);
    }
}
