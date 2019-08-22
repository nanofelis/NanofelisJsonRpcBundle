<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Validator\Constraints;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class RpcRequestValidator extends ConstraintValidator
{
    /**
     * @param mixed      $request
     * @param Constraint $constraint
     */
    public function validate($request, Constraint $constraint)
    {
        try {
            $this->getResolver()->resolve($request);
        } catch (\Exception $e) {
            $this->context->addViolation($e->getMessage());

            return;
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
            ->setAllowedValues('jsonrpc', RpcRequest::JSON_RPC_VERSION)
            ->setAllowedTypes('method', 'string')
            ->setAllowedValues('method', function ($value) {
                return 1 === preg_match('/^\w+\.\w+$/', $value);
            })
            ->setAllowedTypes('id', ['string', 'int'])
            ->setAllowedTypes('params', ['array']);
    }
}
