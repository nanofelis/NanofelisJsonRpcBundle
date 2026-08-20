# Migration Guide

Breaking changes per major release. Version numbers here are the released git tags — the
`docs/` tree used to label these releases "v1" and "v2", which never matched any tag.

- [5.x → 6.0.0](#5x--600)
- [4.x → 5.0.0](#4x--500)

---

## 5.x → 6.0.0

### 1. Only public, non-magic, non-abstract methods are callable

The exposed method surface is now an allowlist. Anything else answers `-32601` (method not found)
instead of being invoked.

This closes a real hole: `#[JsonRpcService]` services exposed their `__construct`, so a caller
could re-run it on the container-shared instance and overwrite its injected dependencies for the
rest of the request — or for the whole worker process under FrankenPHP/RoadRunner. Private and
protected methods previously raised an uncaught `\Error` and surfaced as HTTP 500.

Nothing to change unless you were relying on a magic or non-public method being reachable — in
which case expose it as a public method with an explicit name.

### 2. `RpcBeforeMethodEvent` has been removed

The `nanofelis_json_rpc.before_method` event and its event class are gone. RPC methods now run
through Symfony's standard controller argument lifecycle, so listen to `kernel.controller_arguments`
instead. You get the resolved, typed arguments rather than the raw params array.

Before:

```php
use Nanofelis\JsonRpcBundle\Event\RpcBeforeMethodEvent;

public function onRpcBeforeMethod(RpcBeforeMethodEvent $event): void
{
    $rpcRequest = $event->getRpcRequest();
    $rpcRequest->setParams([...$rpcRequest->getParams(), 'tenant' => $this->tenants->current()]);
}
```

After:

```php
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;

public static function getSubscribedEvents(): array
{
    return [KernelEvents::CONTROLLER_ARGUMENTS => 'onControllerArguments'];
}

public function onControllerArguments(ControllerArgumentsEvent $event): void
{
    [$service, $method] = (array) $event->getController();

    if ($service instanceof MyRpcService) {
        $event->setArguments([...$event->getArguments(), $this->tenants->current()]);
    }
}
```

See [Events Hooks](index.md#events-hooks) for the full example.

### 3. Batch calls return one response per entry

A malformed entry no longer discards the batch. Each entry gets its own response object and the
valid entries still execute; previously a single bad entry produced one error object — not even an
array — and silently dropped every sibling call.

Responses may come back in any order within the array, as the spec permits. **Correlate them on
`id`, never on position.** Clients that relied on the old collapse-to-one-error behaviour must be
updated.

### 4. An empty batch is an invalid request

`[]` now answers `-32600` instead of an empty array: the spec requires a batch to be an array with
at least one value.

### 5. An injected `Request` is the real incoming request

An RPC method type-hinting `Symfony\Component\HttpFoundation\Request` now receives a duplicate of
the incoming HTTP request — real headers, cookies, session, client IP, query string and route
attributes — with the RPC `params` overlaid. It used to be an empty stub, so `getMethod()` now
returns `POST` rather than `GET`. Methods and tests asserting on the old stub must be updated.

The same applies to `kernel.controller_arguments` listeners: the event now also mirrors the
incoming request's *type*, so it is a main-request event for a normal POST. Listeners that
early-return on `!$event->isMainRequest()` — authentication commonly does — now run.

### 6. Raised requirements

- PHP >= 8.4 (was >= 8.0).
- Symfony 6.4 / 7 / 8; Symfony 5 support is dropped.
- `symfony/twig-bundle` and `ext-dom` are no longer required. Neither was used by the bundle, so
  if your project relied on them being pulled in transitively, require them explicitly.

### Also worth knowing

Not breaking, but new in 6.0.0:

- `max_batch_size` (default `100`, `0` disables it) caps how many requests a batch may contain.
  See [Bundle options](index.md#bundle-options).
- `#[JsonRpcService]` is registered for autoconfiguration, so the attribute alone is enough on an
  autoconfigured service — the `nanofelis_json_rpc` tag is no longer needed by hand (it still
  works, and is still required when autoconfiguration is off). Note that adding the attribute is
  therefore sufficient to publish a service over HTTP.
- Duplicate `#[JsonRpcService]` keys, and a service tagged `nanofelis_json_rpc` without the
  attribute, now fail at container build time instead of at request time.

---

## 4.x → 5.0.0

### `getServiceKey()` has been removed

In 4.x and earlier, each RPC service class had to extend `AbstractRpcService` and implement a
static `getServiceKey()` method:

```php
namespace App\RpcServices;

use Nanofelis\JsonRpcBundle\Service\AbstractRpcService;

class MyService extends AbstractRpcService
{
    public static function getServiceKey(): string
    {
        return 'myService';
    }
}
```

In 5.0.0 that method is gone. Use the native PHP 8 attribute `#[JsonRpcService]` instead;
`AbstractRpcService` is no longer required.

```php
namespace App\RpcServices;

use Nanofelis\JsonRpcBundle\Attribute\JsonRpcService;

#[JsonRpcService('myService')]
class MyService
{
}
```

Docs for 4.x and earlier are kept at [docs/v4/index.md](v4/index.md).
