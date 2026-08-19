# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

`nanofelis/json-rpc-bundle` — a Symfony bundle implementing the [JSON-RPC 2.0](https://www.jsonrpc.org/specification) spec. It is a library, not an application: there is no `bin/console`, no app config, and no runnable app. Everything is exercised through the test kernel.

Requires PHP >= 8.4 and Symfony `^6.4|^7.0|^8.0`. The PHP floor is set by Symfony 8, whose packages require >= 8.4.1; Symfony 6.4 and 7 remain supported but are only exercised via `SYMFONY_REQUIRE` in CI, not by the committed lock.

## Commands

Dependencies must be installed first (`composer install`); `vendor/` is not committed.

```bash
# Tests (phpunit-bridge wrapper — installs its own PHPUnit on first run)
vendor/bin/simple-phpunit
vendor/bin/simple-phpunit --filter testMethodName
vendor/bin/simple-phpunit tests/Request/RpcRequestHandlerTest.php

# Coding standards (@Symfony + @Symfony:risky, declare_strict_types enforced)
# Scope is the whole repo minus var/, vendor/ and the generated config/reference.php
vendor/bin/php-cs-fixer fix                          # apply
vendor/bin/php-cs-fixer fix -v --dry-run --diff      # what CI runs

# Static analysis — level 9, src/ only (narrower scope than php-cs-fixer)
vendor/bin/phpstan -n

composer validate --strict --no-check-lock
```

`docker-compose.yml` provides a bare `php` container (`php:8.4-cli`, matching the composer floor) if a local PHP is unavailable.

CI (`.github/workflows/ci.yml`) runs the four checks above on PHP 8.4, and tests Symfony `6.4.*` / `7.*` / `8.*` by combining `SYMFONY_REQUIRE` with `dependency-versions: highest`. The `highest` part is required: `ramsey/composer-install` otherwise installs from the committed lock, which pins versions and silently makes `SYMFONY_REQUIRE` a no-op — the matrix then tests the same locked versions N times while appearing to cover N Symfony releases.

## Architecture

Single POST endpoint (`config/routes.php` → `nanofelis_json_rpc.action.rpc`). All services are wired explicitly in `config/services.php` (PHP DSL) and loaded by `NanofelisJsonRpcExtension`; there is no bundle configuration tree and no `Configuration` class.

Request flow, all through `src/Action/Rpc.php`:

1. **`RpcRequestParser`** — decodes the body into an `RpcPayload` holding one or more `RpcRequest`. Detects batch vs. single via `array_is_list()`. Splits `method` on `.` into `serviceKey` / `methodKey` — exactly two parts or `RpcInvalidRequestException`. Parse/shape errors are caught here and turned into an error response rather than propagating.
2. **`RpcRequestHandler`** — per request: resolve the service, dispatch `RpcBeforeMethodEvent`, resolve arguments, invoke, normalize.
3. **`RpcResponder`** — unwraps to a single object for non-batch payloads, an array for batch. Always HTTP 200 with a `JsonResponse`; JSON-RPC errors live in the body.

Key design points to preserve when changing things:

- **Service discovery is two-part.** A class is exposed only if it carries the DI tag `nanofelis_json_rpc` *and* the `#[JsonRpcService('key')]` attribute. The tag makes it visible to `ServiceFinder` via `tagged_iterator`; the attribute supplies the lookup key. There is **no** `registerAttributeForAutoconfiguration`, so the attribute alone does nothing — users still tag in `services.yaml`. A tagged service without the attribute throws `RpcServiceKeyMissingException` at container-build/first-use time, not per request.
- **Argument resolution reuses Symfony's controller machinery, including the kernel event.** `RpcRequestHandler` builds a synthetic `Request` with the RPC `params` in *both* `request` and `attributes`, plus a forced `CONTENT_TYPE: application/x-www-form-urlencoded`, then calls `argument_resolver`. It then dispatches a `ControllerArgumentsEvent` (`KernelEvents::CONTROLLER_ARGUMENTS`) and re-reads the controller and arguments back off the event. That dispatch is load-bearing, not decorative: `RequestPayloadValueResolver` does its work in `onKernelControllerArguments`, so `#[MapRequestPayload]` only functions because of it — hence the `HttpKernelInterface` injected as the handler's 5th constructor arg (`config/services.php`). Any change to param handling must preserve all three pieces (dual request/attributes population, content type, event dispatch) rather than bypassing the resolver.
- **Result normalization is always on.** Every return value passes through the Symfony `serializer`. `#[RpcNormalizationContext([...])]` on the method supplies the normalization context, read reflectively via `ServiceDescriptor::getMethodAttribute()`.
- **Only `AbstractRpcException` subclasses become JSON-RPC errors.** Anything else rethrows and surfaces as a real 500. Reserved codes and their default messages are constants on `AbstractRpcException`; `RpcApplicationException` guards against user codes falling in the reserved `-32099..-32000` range.
- **`TypeError` disambiguation** (`RpcRequestHandler::isInvalidParamsException`) inspects the trace frame to tell "caller passed bad params" from "the method itself blew up", mapping only the former to `-32602`. Fragile by nature — keep its test coverage.

## Testing

`tests/TestKernel.php` is a `MicroKernelTrait` kernel that registers FrameworkBundle and this bundle, imports `config/routes.php`, and — via its own `CompilerPassInterface` — registers `tests/Service/MockService.php` with the `nanofelis_json_rpc` tag. `KERNEL_CLASS` is set in `phpunit.xml.dist`.

Add new exposable RPC methods to `MockService` when covering handler/resolver behaviour end-to-end (`tests/Action/RpcTest.php` uses `WebTestCase` + `KernelBrowser`); unit-level tests instantiate collaborators directly (`ServiceFinderTest`).

`SYMFONY_DEPRECATIONS_HELPER=max[self]=0` — deprecations triggered by this bundle's own code fail the suite.

Booting the test kernel makes Symfony write `config/reference.php` — auto-generated app-only scaffolding, not part of the bundle. It is gitignored and excluded from php-cs-fixer; leave both guards in place rather than formatting or committing the file.

## Versioning

`master` is the 2.x line (`dev-master` → `2.0-dev`). v2 dropped `AbstractRpcService::getServiceKey()` in favour of `#[JsonRpcService]`; see `docs/migration.md`. Docs are split: `docs/v2/index.md` (current), `docs/v1/index.md` (legacy). User-facing behaviour changes belong in `docs/v2/index.md` and `CHANGELOG.md`.

2.0.0 has not shipped yet. `CHANGELOG.md` carries a `## Unreleased` section above the still-undated `## [2.0.0] - Unreleased`; append to one of those rather than starting a new version heading.
