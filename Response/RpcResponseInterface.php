<?php

namespace Nanofelis\Bundle\JsonRpcBundle\Response;

interface RpcResponseInterface
{
    public function getContent(): array;
}