The NanofelisJsonRpcBundle is a symfony friendly implementation of the  [JSON-RPC 2.0](https://www.jsonrpc.org/specification) specification.

⚠️ Version 2.x – Breaking Change  
This version introduces a breaking change:  
- All RPC services must now use the `#[JsonRpcService('serviceKey')]` attribute.  
- The method `getServiceKey()` has been removed.  
- PHP 8.0+ is required due to native attributes.

Installation
=============

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
=============

- 📘 Documentation for version **2.x** (current): [docs/v2/index.md](docs/v2/index.md)
- 📘 Documentation for version **1.x** (legacy): [docs/index.md](docs/v1/index.md)
- 🔁 Upgrade from v1 to v2: [docs/migration.md](docs/migration.md).
