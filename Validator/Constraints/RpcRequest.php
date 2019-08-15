<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class RpcRequest extends Constraint
{
    const JSON_RPC_VERSION = '2.0';
}
