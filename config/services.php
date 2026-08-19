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

    // max_batch_size is injected by NanofelisJsonRpcExtension from the bundle configuration
    $services->set('nanofelis_json_rpc.request.parser', RpcRequestParser::class)
        ->args([
            abstract_arg('max batch size'),
        ]);

    $services->set('nanofelis_json_rpc.request.handler', RpcRequestHandler::class)
        ->args([
            service('argument_resolver'),
            service('nanofelis_json_rpc.service.finder'),
            service('serializer'),
            service('event_dispatcher'),
            service('kernel'),
        ]);

    // the service locator argument is injected by RpcServicePass, which indexes every service
    // tagged "nanofelis_json_rpc" by its #[JsonRpcService] key at container build time
    $services->set('nanofelis_json_rpc.service.finder', ServiceFinder::class)
        ->args([
            abstract_arg('rpc services locator'),
        ]);

    $services->set('nanofelis_json_rpc.response.rpc_responder', RpcResponder::class);
};
