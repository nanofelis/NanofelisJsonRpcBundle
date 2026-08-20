<?php

declare(strict_types=1);

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->add('nanofelis_json_rpc.endpoint', '/')
        ->controller('nanofelis_json_rpc.action.rpc')
        ->methods(['POST']);
};
