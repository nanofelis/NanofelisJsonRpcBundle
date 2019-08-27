[![Build Status](https://travis-ci.com/nanofelis/NanofelisJsonRpcBundle.svg?branch=master)](https://travis-ci.com/nanofelis/NanofelisJsonRpcBundle)

The NanofelisJsonRpcBundle is a symfony friendly implementation of the  [JSON-RPC 2.0](https://www.jsonrpc.org/specification) specification.

Installation
============

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
$ composer require nanofelis/json-rpc-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
$ composer require nanofelis/json-rpc-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `app/AppKernel.php` file of your project:

```php
<?php
// app/AppKernel.php

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // ...
            new Nanofelis\Bundle\JsonRpcBundle\NanofelisJsonRpcBundle(),
        ];
    }
}
```


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
    controller: Nanofelis\Bundle\JsonRpcBundle\Action\Rpc
    methods: GET|POST
```  

Usage
=====

Simply Tag the services you want to expose and send a json-rpc payload to the RPC endpoint.

The method key must follow the convention  `{className with first letter lower cased}.{method}`
 
```yaml
# config/services.yaml

App\RpcServices:
    resource: src/RpcServices
    tag: ['nanofelis_json_rpc']         
```

```php
namespace App\RpcServices;

class MyService 
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

Param Conversion
----------------
The bundle supports the [Param Converter](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/converters.html) from the [SensioFrameworkExtraBundle](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/index.html#annotations-for-controllers)

As for a regular controller method, dates and Doctrine entities are automatically converted if a parameter's name matches a method argument with the correct type hinting. 

```php
namespace App\RpcServices;

class MyService 
{
    function workWithEntity(MyEntity $entity, \DateTime $date)
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
Responses are always processed by a Symfony normalizer. If you need to specify a normalization context, you can use the `RpcNormalizationContext` annotation:

```php
namespace App\RpcServices;

use Nanofelis\Bundle\JsonRpcBundle\Annotation\RpcNormalizationContext;

class MyService 
{
    /**
     * RpcNormalizationContext(context={'custom'})
     */
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
You can hook to the 2 following events in the rpc request lifecycle:

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

__nanofelis_json_rpc.before_response__  
__Event Class__: RpcBeforeResponseEvent

This event is dispatched just before the error or success response is sent. You can use it to alter the RPC response data.

```php
use Symfony\Component\HttpKernel\Event\ControllerEvent;

public function onRpcBeforeResponse(RpcBeforeResponseEvent $event)
{
    $rpcRequest = $event->getRpcRequest();
    $rpcResponse = $rpcRequest->getReponse() ?: $rpcRequest->getReponseError() 
}
```

GET Support
-----------
The bundle supports GET requests with http query params payload. 
It also supports the [cache annotation](https://symfony.com/doc/current/bundles/SensioFrameworkExtraBundle/annotations/cache.html).

```shell script
# Example GET call
curl http://localhost?jsonrpc=2.0&method=myService.add&params[0]=1&params[1]=2&id=test-call | fx this

{
  "jsonrpc": "2.0",
  "result": 3,
  "id": "test-call"
}
```
![warning](https://img.icons8.com/color/48/000000/warning-shield.png) GET requests are not recommended for RPC payloads.
Unlike a json payload, all query params are sent as string, so for instance the `add(int $a, int $b)` method would fail as 
an invalid param request if the file contained a `declare(strict_types=1);`
It is a better option to use regular api endpoints next to the RPC route for specific GET purposes.
