<?php

declare(strict_types=1);

namespace Nanofelis\JsonRpcBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class RequestPayload extends Constraint
{
    const JSON_RPC_VERSION = '2.0';
}
