<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Tests\Validator\Constraints;

use Nanofelis\Bundle\JsonRpcBundle\Validator\Constraints\RpcRequest;
use Nanofelis\Bundle\JsonRpcBundle\Validator\Constraints\RpcRequestValidator;
use Nanofelis\Bundle\JsonRpcBundle\Validator\Constraints\RpcRequest;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class RpcRequestValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return  new RpcRequestValidator();
    }

    /**
     * @dataProvider provideRawPayload
     */
    public function testValidate(array $payload, $expectedViolation = false)
    {
        $this->validator->validate($payload, new RpcRequest());

        $this->assertSame($expectedViolation, $this->context->getViolations()->count() > 0);
    }

    public function provideRawPayload(): \Generator
    {
        $validPayload = [
            'jsonrpc' => RpcRequest::JSON_RPC_VERSION,
            'id' => 'adding',
            'method' => 'mock.add',
            'params' => [1, 2],
        ];

        yield [$validPayload];

        $missingJsonRpcVersion = [
            'method' => 'mock.add',
        ];

        yield [$missingJsonRpcVersion, true];

        $missingServiceId = [
            'method' => 'add',
        ];

        yield [$missingServiceId, true];

        $invalidParamsFormat = [
            'method' => 'mock.add',
            'params' => '1,2',
        ];

        yield [$invalidParamsFormat, true];
    }
}
