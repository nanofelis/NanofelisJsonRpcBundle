<?php

declare(strict_types=1);

namespace Nanofelis\Bundle\JsonRpcBundle\Doc;

use Nanofelis\Bundle\JsonRpcBundle\Service\AbstractRpcService;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Twig\Error\Error;

class DocResponder
{
    public function __construct(private Environment $environment)
    {
    }

    /**
     * @param array<string,\ReflectionClass<AbstractRpcService>> $rpcServices indexed by services keys
     *
     * @throws Error
     */
    public function __invoke(array $rpcServices): Response
    {
        return new Response($this->environment->render('@NanofelisJsonRpc/doc.html.twig', ['rpcServices' => $rpcServices]));
    }
}
