## Unreleased

### ⚠️ Breaking changes

- Reduced the exposed RPC surface. Only public, non-magic, non-abstract methods are callable;
  everything else now answers `-32601` instead of being invoked. This closes an issue where
  `#[JsonRpcService]` services exposed their `__construct` — letting a caller re-run it on the
  shared instance and overwrite injected dependencies — as well as other magic methods.
  Non-public methods previously raised an uncaught `\Error` and surfaced as HTTP 500.

### Added

- `max_batch_size` configuration option (default `100`, `0` disables the limit). Batch calls
  larger than the limit are rejected with `-32600` instead of being executed unbounded.
- Duplicate `#[JsonRpcService]` keys are now detected at container build time. Previously the
  last tagged service silently won.

### Fixed

- Run RPC methods through Symfony's standard controller argument lifecycle, including the `kernel.controller_arguments` event.
- Only the RPC service named by a request is instantiated. Service keys are now resolved at
  container build time by a compiler pass and exposed through a service locator; previously
  every tagged service — and its whole dependency graph — was constructed on every request,
  then reflected over to recover its key.
- A service tagged `nanofelis_json_rpc` without the `#[JsonRpcService]` attribute now fails at
  container build time rather than throwing on every request, where it surfaced as an HTTP 500.

## [2.0.0] - Unreleased

### ⚠️ Breaking changes

- Removed support for `getServiceKey()` method.
- All RPC services must now use the `#[JsonRpcService('serviceKey')]` attribute.
- Introduced usage of PHP 8 attributes for service identification.
- Dropped support for Symfony 5.
- Raised the minimum PHP version to 8.4.
- Removed the `symfony/twig-bundle` and `ext-dom` requirements. Neither was used by
  the bundle; projects relying on them being pulled in transitively must now require
  them explicitly.

### Added

- Support for Symfony 8.

### Requirements

- PHP >= 8.4, Symfony 6.4 / 7 / 8.
