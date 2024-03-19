<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\tests\Service;

use Nanofelis\JsonRpcBundle\Attribute\RpcNormalizationContext;
use Nanofelis\JsonRpcBundle\Service\AbstractRpcService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

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
        throw new \Exception('it went wrong', 99);
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
