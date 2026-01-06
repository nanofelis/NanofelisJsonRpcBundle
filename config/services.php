<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Nanofelis\JsonRpcBundle\Action\Rpc;
use Nanofelis\JsonRpcBundle\Request\RpcRequestHandler;
use Nanofelis\JsonRpcBundle\Request\RpcRequestParser;
use Nanofelis\JsonRpcBundle\Responder\RpcResponder;
use Nanofelis\JsonRpcBundle\Service\ServiceFinder;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    $services->set('nanofelis_json_rpc.action.rpc', Rpc::class)
        ->public()
        ->args([
            service('nanofelis_json_rpc.request.parser'),
            service('nanofelis_json_rpc.request.handler'),
            service('nanofelis_json_rpc.response.rpc_responder'),
        ]);

    $services->set('nanofelis_json_rpc.request.parser', RpcRequestParser::class);

    $services->set('nanofelis_json_rpc.request.handler', RpcRequestHandler::class)
        ->args([
            service('argument_resolver'),
            service('nanofelis_json_rpc.service.finder'),
            service('serializer'),
            service('event_dispatcher'),
        ]);

    $services->set('nanofelis_json_rpc.service.finder', ServiceFinder::class)
        ->args([
            tagged_iterator('nanofelis_json_rpc'),
        ]);

    $services->set('nanofelis_json_rpc.response.rpc_responder', RpcResponder::class)
        ->args([
            service('event_dispatcher'),
        ]);
};
