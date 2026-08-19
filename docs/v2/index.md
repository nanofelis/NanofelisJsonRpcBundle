
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

Add the `#[JsonRpcService]` attribute to the services you want to expose and send a json-rpc
payload to the RPC endpoint.

The method parameter must follow the convention `{serviceKey}.{method}`

The attribute is registered for autoconfiguration, so an autoconfigured service (the default in
`services.yaml`) needs nothing else. Services with autoconfiguration disabled must be tagged
`nanofelis_json_rpc` by hand. Either way the attribute is mandatory: a tagged service without it,
or two services declaring the same key, fails at container build time rather than at request time.

Only **public, non-magic, non-abstract** methods are reachable. Anything else — private or
protected methods, `__construct` and other magic methods — answers `method not found`
(`-32601`). Keep this in mind when exposing a service: every other public method on the class
is part of your API surface, so prefer dedicated, narrow RPC service classes.

```yaml
# config/services.yaml

# autoconfigure is enabled by default, so the attribute alone is enough;
# add tag: ['nanofelis_json_rpc'] if you disable it
App\RpcServices:
    resource: src/RpcServices
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

Errors are isolated per entry: a malformed entry does not affect its siblings, which are still
executed. As the RFC allows, responses may come back in any order within the array — correlate them
on `id`.

```shell script
# One bad entry among valid ones
curl -d '[{"jsonrpc": "2.0", "method": "myService.add", "params": [1, 2], "id": "ok"}, {"jsonrpc": "2.0", "method": "noSeparator", "id": "bad"}]'  http://localhost | fx this

[
  {
    "jsonrpc": "2.0",
    "result": 3,
    "id": "ok"
  },
  {
    "jsonrpc": "2.0",
    "error": {
      "code": -32600,
      "message": "invalid json-rpc payload",
      "data": null
    },
    "id": "bad"
  }
]
```

Only a failure to recognise the batch *itself* produces a single error object instead of an array:
invalid JSON, a payload that is not an array, an empty array, or a batch exceeding
`max_batch_size`.

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
This bundle exposes no event of its own. Because RPC methods go through Symfony's standard
controller argument lifecycle, hook into `kernel.controller_arguments` instead — it is dispatched
after the arguments are resolved and before the method is invoked, and it lets you inspect or
replace the already-typed arguments rather than raw params.

```php
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RpcArgumentsSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER_ARGUMENTS => 'onControllerArguments'];
    }

    public function onControllerArguments(ControllerArgumentsEvent $event): void
    {
        [$service, $method] = (array) $event->getController();

        // narrow to your own rpc services, then adjust the resolved arguments
        if ($service instanceof MyRpcService) {
            $event->setArguments([...$event->getArguments(), $this->tenants->current()]);
        }
    }
}
```
