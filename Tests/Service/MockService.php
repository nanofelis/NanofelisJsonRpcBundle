<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Service;

class MockService
{
    public function add(int $arg1, int $arg2): int
    {
        return $arg1 + $arg2;
    }

    public function getDateIso(\DateTime $date): string
    {
        return $date->format(\DateTime::ISO8601);
    }

    /**
     * @throws \Exception
     */
    public function willThrowBadArguments()
    {
        throw new \InvalidArgumentException('bad arguments');
    }

    /**
     * @throws \Exception
     */
    public function willThrowException()
    {
        throw new \Exception('it went wrong');
    }

    /**
     * @throws \Error
     */
    public function willThrowPhpError()
    {
        throw new \Error();
    }
}
