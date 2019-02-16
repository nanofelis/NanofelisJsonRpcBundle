<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Tests\Validator\Constraints;

use Nanofelis\JsonRpcBundle\Validator\Constraints\RequestPayload;
use Nanofelis\JsonRpcBundle\Validator\Constraints\RequestPayloadValidator;
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

class RequestPayloadValidatorTest extends ConstraintValidatorTestCase
{
    protected function createValidator()
    {
        return  new RequestPayloadValidator();
    }

    /**
     * @dataProvider provideRawPayload
     */
    public function testValidate(array $payload, $expectedViolation = false)
    {
        $this->validator->validate($payload, new RequestPayload());

        $this->assertSame($expectedViolation, $this->context->getViolations()->count() > 0);
    }

    public function provideRawPayload(): \Generator
    {
        $validPayload = [
            'jsonrpc' => RequestPayload::JSON_RPC_VERSION,
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
