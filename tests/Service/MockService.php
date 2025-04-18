<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Service;

use Nanofelis\JsonRpcBundle\Attribute\JsonRpcService;
use Nanofelis\JsonRpcBundle\Attribute\RpcNormalizationContext;
use Nanofelis\JsonRpcBundle\Exception\RpcApplicationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[JsonRpcService('mockService')]
class MockService
{
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

    #[RpcNormalizationContext(['test'])]
    public function returnObject(): \stdClass
    {
        return (object) ['prop' => 'test'];
    }

    /**
     * @throws \Exception
     */
    public function willThrowException()
    {
        throw new RpcApplicationException('it went wrong', 99);
    }

    public function withNullables(?string $a = null, ?string $b = null): array
    {
        return ['a' => $a, 'b' => $b];
    }

    /**
     * @return array{a:int, b: string, c: bool}
     */
    public function withMapRequest(#[MapRequestPayload] MockDTO $fakeDTO): array
    {
        return ['a' => $fakeDTO->a, 'b' => $fakeDTO->b, 'c' => $fakeDTO->c];
    }
}
