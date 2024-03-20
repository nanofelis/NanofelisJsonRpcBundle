
Configuration
============

Load the routing configuration
------------------------------
Import the main routing file or create a custom route:

```yaml
# config/routes.yaml
rpc:
    resource: '@NanofelisJsonRpcBundle/Resources/config/routing.xml'
```  
or
```yaml
# config/routes.yaml
rpc:
    path: /
    controller: nanofelis_json_rpc.action.rpc
    methods: POST

rpc_doc:
    path: /doc
    controller: nanofelis_json_rpc.action.doc
    methods: GET
```  

Usage
=====

Simply Tag the services you want to expose and send a json-rpc payload to the RPC endpoint.

The method parameter must follow the convention `{serviceKey}.{method}`

```yaml
# config/services.yaml

App\RpcServices:
    resource: src/RpcServices
    tag: ['nanofelis_json_rpc']         
```

```php
namespace App\RpcServices;

use Nanofelis\JsonRpcBundle\Service\AbstractRpcService;

class MyService extends AbstractRpcService
{
    public static function getServiceKey(): string
    {
        return 'myService';
    }
    
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

As for a regular controller method, dates and Doctrine entities for example are automatically converted if a parameter's name matches a method argument with the correct type hinting or attribute.

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
