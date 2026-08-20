## [6.0.0]

### ⚠️ Breaking changes

- Reduced the exposed RPC surface. Only public, non-magic, non-abstract methods are callable;
  everything else now answers `-32601` instead of being invoked. This closes an issue where
  `#[JsonRpcService]` services exposed their `__construct` — letting a caller re-run it on the
  shared instance and overwrite injected dependencies — as well as other magic methods.
  Non-public methods previously raised an uncaught `\Error` and surfaced as HTTP 500.
- A malformed entry in a batch call no longer discards the batch. Each entry now gets its own
  response, and the valid entries are executed; previously one bad entry produced a single error
  object — not an array — and silently dropped every sibling call. As the spec allows, responses
  may be returned in any order within the array; correlate them on `id`. Clients relying on the
  old collapse-to-one-error behaviour must be updated.
- Removed `RpcBeforeMethodEvent` and the `nanofelis_json_rpc.before_method` event. RPC methods run
  through Symfony's standard controller argument lifecycle, so listen to `kernel.controller_arguments`
  instead — it gives access to the resolved, typed arguments rather than raw params.
- An empty batch (`[]`) now answers `-32600` rather than an empty array, as the spec requires a
  batch to be an array with at least one value.
- An RPC method that injects `Symfony\Component\HttpFoundation\Request` now receives the incoming
  request rather than an empty stub, so `getMethod()` returns `POST` instead of `GET`. Methods
  asserting on the old stub must be updated.
- Dropped support for Symfony 5.
- Raised the minimum PHP version to 8.4.
- Removed the `symfony/twig-bundle` and `ext-dom` requirements. Neither was used by
  the bundle; projects relying on them being pulled in transitively must now require
  them explicitly.

### Added

- `max_batch_size` configuration option (default `100`, `0` disables the limit). Batch calls
  larger than the limit are rejected with `-32600` instead of being executed unbounded.
- Duplicate `#[JsonRpcService]` keys are now detected at container build time. Previously the
  last tagged service silently won.
- `#[JsonRpcService]` is registered for autoconfiguration, so an autoconfigured service carrying
  the attribute no longer needs the `nanofelis_json_rpc` tag applied by hand. The tag still works
  and remains required when autoconfiguration is disabled. Note this makes adding the attribute
  alone sufficient to publish a service over HTTP.
- Support for Symfony 8.

### Fixed

- Run RPC methods through Symfony's standard controller argument lifecycle, including the `kernel.controller_arguments` event.
- Only the RPC service named by a request is instantiated. Service keys are now resolved at
  container build time by a compiler pass and exposed through a service locator; previously
  every tagged service — and its whole dependency graph — was constructed on every request,
  then reflected over to recover its key.
- A service tagged `nanofelis_json_rpc` without the `#[JsonRpcService]` attribute now fails at
  container build time rather than throwing on every request, where it surfaced as an HTTP 500.
- An `id` that is neither a string nor an integer (a float such as `1.5`, or an array) answers
  `-32600` instead of raising a `TypeError` and surfacing as HTTP 500.
- A request without a `jsonrpc` key no longer raises an "Undefined array key" PHP warning on its
  way to the correct `-32600` response.
- The request used to resolve method arguments is now a duplicate of the incoming HTTP request —
  headers, cookies, session, client IP, query string and route attributes included — with the RPC
  `params` and a form content type swapped in and the body emptied. It was an empty carrier before,
  so a `kernel.controller_arguments` listener could not read an `Authorization` header, a cookie or
  the client IP off it.
- The `kernel.controller_arguments` event now mirrors the incoming request's type instead of
  claiming to be a sub-request, so it is `MAIN_REQUEST` for a normal POST. App listeners that skip
  sub-requests — a common guard, and often where authentication lives — run again. The duplicate is
  still not pushed onto the `RequestStack`, so services injecting it see the real incoming request.

### Requirements

- PHP >= 8.4, Symfony 6.4 / 7 / 8.

## [5.0.0] - 2025-04-22

### ⚠️ Breaking changes

- Removed support for `getServiceKey()` method.
- All RPC services must now use the `#[JsonRpcService('serviceKey')]` attribute.
- Introduced usage of PHP 8 attributes for service identification.
