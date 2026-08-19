
Configuration
============

Load the routing configuration
------------------------------
Import the main routing file or create a custom route:

```yaml
# config/routes.yaml
rpc:
    resource: '@NanofelisJsonRpcBundle/config/routes.php'
    type: php
```  
or
```yaml
# config/routes.yaml
rpc:
    path: /
    controller: nanofelis_json_rpc.action.rpc
    methods: POST
```  

Bundle options
--------------
The bundle works without any configuration. The only option limits how many requests a single
batch call may contain:

```yaml
# config/packages/nanofelis_json_rpc.yaml
nanofelis_json_rpc:
    max_batch_size: 100    # default; set to 0 to disable the limit
```

Batches larger than the limit are rejected with an `-32600` (invalid request) error instead of
being executed.

Usage
=====

Simply Tag the services you want to expose and send a json-rpc payload to the RPC endpoint.

The method parameter must follow the convention `{serviceKey}.{method}`

A tagged service must carry the `#[JsonRpcService]` attribute; a missing attribute, or two
services declaring the same key, fails at container build time rather than at request time.

Only **public, non-magic, non-abstract** methods are reachable. Anything else — private or
protected methods, `__construct` and other magic methods — answers `method not found`
(`-32601`). Keep this in mind when exposing a service: every other public method on the class
is part of your API surface, so prefer dedicated, narrow RPC service classes.

```yaml
# config/services.yaml

App\RpcServices:
    resource: src/RpcServices
    tag: ['nanofelis_json_rpc']         
```

```php
namespace App\RpcServices;

use Nanofelis\JsonRpcBundle\Attribute\JsonRpcService;

#[JsonRpcService('myService')]
{    
    function add(int $a, int $b): int
    {
        return $a + $b;
    }
}
```

```shell script
# Example call with success response
curl -d '{"jsonrpc": "2.0", "method": "myService.add", "params": [1, 2], "id": "test-call"}'  http://localhost | fx this

{
  "jsonrpc": "2.0",
  "result": 3,
  "id": "test-call"
}


# Example call with wrong method parameters
curl -d '{"jsonrpc": 2.0, "method": "myService.add", "params": [1], "id": "test-call"}'  http://localhost | fx this

{
  "jsonrpc": "2.0",
  "error": {
    "code": -32602,
    "message": "invalid params",
    "data": null
  },
  "id": "test-call"
}

```

Only exceptions that extend the [AbstractRpcException.php](../src/Exception/AbstractRpcException.php) will be cast
to a [JSON-RPC error](https://www.jsonrpc.org/specification#error_object).

Batch Requests
--------------
As described by the RFC, multiple requests can be sent in a single call.

 ```shell script
# Example batch call
curl -d '[{"jsonrpc": "2.0", "method": "myService.add", "params": [1, 2], "id": "test-call-0"}, {"jsonrpc": "2.0", "method": "myService.add", "params": [3, 4], "id": "test-call-0"}]'  http://localhost | fx this

[
  {
    "jsonrpc": "2.0",
    "result": 3,
    "id": "test-call-0"
  },
  {
    "jsonrpc": "2.0",
    "result": 7,
    "id": "test-call-1"
  }
]

```

Arguments Resolver
----------------
This bundle supports the built-in [Argument Resolver](https://symfony.com/doc/current/controller/value_resolver.html) from the Symfony Core for RPC methods.

RPC methods use the same controller argument lifecycle as regular Symfony controllers. Once arguments have been resolved, the bundle dispatches Symfony's `kernel.controller_arguments` event before invoking the RPC method. Framework and application listeners can therefore inspect, validate or replace the resolved arguments through the standard Symfony extension points.

Dates and Doctrine entities, for example, are automatically converted if a parameter's name matches a method argument with the correct type hinting or attribute. JSON-RPC parameters are also exposed through the request used by Symfony's argument resolver.

```php
namespace App\RpcServices;

class MyService 
{
    function workWithEntity(MyEntity $entity, #[MapDateTime(format: 'Y-m-d')] \DateTime $date)
    {
        //
    }
}

```shell script
# Example call with type hinting
curl -d '{'jsonrpc': "2.0", "method": "myService.workWithEntity", "params": ["entity": 1, "date": "2017-01-01"]}'  http://localhost
```

Normalization and Contexts
--------------------------
Responses are always processed by a Symfony normalizer. If you need to specify a normalization context, you can use the `RpcNormalizationContext` attribute:

```php
namespace App\RpcServices;

use Nanofelis\JsonRpcBundle\Attribute\RpcNormalizationContext;

class MyService 
{
    
    #[RpcNormalizationContext(contexts: ['custom'])]
    function doSomething($data): Article
    {
        $article = $this->handler($data);
 
        return $article;
    }
}
```

```php
namespace App\Normalizer;

use App\Entity\Article;

class ArticleNormalizer implements NormalizerInterface
{
    public function normalize($vehicle, $format = null, array $context = [])
    {
        if (in_array('custom', $context) {
            ...
        }
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof Article;
    }
}
```

Events Hooks
------------
You can hook to the following event in the rpc request lifecycle:

__nanofelis_json_rpc.before_method__  
__Event Class__: RpcBeforeMethodEvent

This event is dispatched just before the method execution. You can use it to alter the rpc request params.
```php
use Symfony\Component\HttpKernel\Event\ControllerEvent;

public function onRpcBeforeMethod(RpcBeforeMethodEvent $event)
{
    $rpcRequest = $event->getRpcRequest();
    $serviceDescriptor = $event->getServiceDescriptor();
}
```
