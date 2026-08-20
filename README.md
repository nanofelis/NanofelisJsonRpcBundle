The NanofelisJsonRpcBundle is a symfony friendly implementation of the  [JSON-RPC 2.0](https://www.jsonrpc.org/specification) specification.

Requires PHP >= 8.4 and Symfony 6.4 / 7 / 8.

⚠️ **Breaking changes**

**5.0.0** — all RPC services must use the `#[JsonRpcService('serviceKey')]` attribute; the
`getServiceKey()` method has been removed.

**6.0.0 (unreleased)** — raises the floor to PHP 8.4 and Symfony 6.4, restricts the callable
method surface to public non-magic methods, removes `RpcBeforeMethodEvent` in favour of Symfony's
`kernel.controller_arguments`, and returns one response per batch entry.

See the [migration guide](docs/migration.md) and [CHANGELOG.md](CHANGELOG.md).

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

- 📘 Documentation (current): [docs/index.md](docs/index.md)
- 📘 Documentation for **4.x and earlier** (legacy): [docs/v4/index.md](docs/v4/index.md)
- 🔁 Upgrading between major versions: [docs/migration.md](docs/migration.md)
