The NanofelisJsonRpcBundle is a symfony friendly implementation of the  [JSON-RPC 2.0](https://www.jsonrpc.org/specification) specification.

Installation
============

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Applications that use Symfony Flex
----------------------------------

Open a command console, enter your project directory and execute:

```console
composer require nanofelis/json-rpc-bundle
```

Applications that don't use Symfony Flex
----------------------------------------

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require nanofelis/json-rpc-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Nanofelis\JsonRpcBundle\NanofelisJsonRpcBundle::class => ['all' => true],
];
```

Documentation
============

Documentation is found in [docs/index.md](docs/index.md).
