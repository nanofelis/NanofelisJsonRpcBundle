<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Validator\Constraints;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RequestPayloadValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @param mixed                     $value      The value that should be validated
     * @param Constraint|RequestPayload $constraint The constraint for the validation
     */
    public function validate($value, Constraint $constraint)
    {
        try {
            $this->getResolver()->resolve($value);
        } catch (\Exception $e) {
            $this->context->addViolation($e->getMessage());

            return;
        }

        if (!preg_match('/^\w+\.\w+$/', $value['method'])) {
            $this->context->addViolation('Invalid RPC method format %method%, needs "serviceKey.method"', ['%method%' => $value['method']]);
        }
    }

    private function getResolver(): OptionsResolver
    {
        $resolver = new OptionsResolver();

        return $resolver
            ->setRequired([
                'jsonrpc',
                'method',
            ])
            ->setDefined(['id'])
            ->setDefined(['params'])
            ->setAllowedValues('jsonrpc', RequestPayload::JSON_RPC_VERSION)
            ->setAllowedTypes('method', 'string')
            ->setAllowedTypes('id', ['string', 'int'])
            ->setAllowedTypes('params', ['array'])
        ;
    }
}
