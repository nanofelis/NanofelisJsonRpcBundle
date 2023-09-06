<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Action;

use Nanofelis\Bundle\JsonRpcBundle\Doc\DocResponder;
use Nanofelis\Bundle\JsonRpcBundle\Service\AbstractRpcService;
use Nanofelis\Bundle\JsonRpcBundle\Service\ServiceFinder;
use Symfony\Component\HttpFoundation\Response;
use Twig\Error\Error;

class Doc
{
    public function __construct(private DocResponder $docResponder, private ServiceFinder $serviceFinder)
    {
    }

    /**
     * @throws Error|\ReflectionException
     */
    public function __invoke(): Response
    {
        $rpcServices = array_map(
            fn (AbstractRpcService $rpcService) => new \ReflectionClass($rpcService),
            iterator_to_array($this->serviceFinder->getRpcServices())
        );

        return ($this->docResponder)($rpcServices);
    }
}
