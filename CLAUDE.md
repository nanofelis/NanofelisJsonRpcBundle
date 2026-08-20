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

Single POST endpoint (`config/routes.php` → `nanofelis_json_rpc.action.rpc`). All services are wired explicitly in `config/services.php` (PHP DSL) and loaded by `NanofelisJsonRpcExtension`. The configuration tree is deliberately minimal — `src/DependencyInjection/Configuration.php` exposes only `max_batch_size`.

Two `config/services.php` arguments are `abstract_arg()` placeholders, so the container fails to compile if their provider ever stops running: the parser's batch limit (filled by `NanofelisJsonRpcExtension::load()`) and the finder's service locator (filled by `RpcServicePass`).

Request flow, all through `src/Action/Rpc.php`:

1. **`RpcRequestParser`** — decodes the body into an `RpcPayload` holding one or more `RpcRequest`. Detects batch vs. single via `array_is_list()`. Splits `method` on `.` into `serviceKey` / `methodKey` — exactly two parts or `RpcInvalidRequestException`. Errors are turned into responses here rather than propagating, but at **two different levels**, and the distinction is the spec's: a *batch-level* failure (bad JSON, not an array, empty array, over `max_batch_size`) throws out to `parse()`'s catch and yields one error object; an *entry-level* failure inside a recognised batch is caught per iteration and yields one error at that entry's position, leaving its siblings to run.
2. **`RpcRequestHandler`** — per request: resolve the service, resolve arguments, dispatch `kernel.controller_arguments`, invoke, normalize. The bundle exposes **no event of its own** — `RpcBeforeMethodEvent` was removed in favour of the standard Symfony hook, which sees resolved typed arguments rather than raw params.
3. **`RpcResponder`** — unwraps to a single object for non-batch payloads, an array for batch. Always HTTP 200 with a `JsonResponse`; JSON-RPC errors live in the body. Batch responses are in no guaranteed order (the spec permits any), so don't add machinery to align them — clients correlate on `id`.

Key design points to preserve when changing things:

- **Service discovery is attribute-driven and resolved at build time.** `#[JsonRpcService('key')]` is registered for autoconfiguration in `NanofelisJsonRpcBundle::build()`, so an autoconfigured class carrying it is tagged automatically; the `nanofelis_json_rpc` tag is still honoured and is still required when autoconfiguration is off (which is why `tests/TestKernel.php`, registering fixtures by hand, keeps exercising the explicit-tag path). `RpcServicePass` (`src/DependencyInjection/Compiler/RpcServicePass.php`) reads the attribute during compilation, throws `RpcServiceKeyMissingException` for a tagged-but-unattributed service and rejects duplicate keys, then hands `ServiceFinder` a `ServiceLocator` keyed by service key. **Do not go back to `tagged_iterator` here**: iterating it instantiates every tagged service on every request, which is what this design exists to avoid. `tests/Action/RpcTest::testUnusedServicesAreNotInstantiated` is the regression guard.
- **The pass runs at `TYPE_OPTIMIZE`, not the usual `TYPE_BEFORE_OPTIMIZATION`.** A kernel implementing `CompilerPassInterface` is registered by Symfony at `BEFORE_OPTIMIZATION` priority `-10000`, i.e. last in that stage — so a bundle pass there cannot see services such a kernel registers (exactly what `tests/TestKernel.php` does). Moving the pass earlier silently yields an empty locator and every call answering `-32601`.
- **Method exposure is an allowlist.** `ServiceDescriptor::__construct` is the single choke point and rejects non-public, abstract and `__*` methods with `RpcMethodNotFoundException`. This is load-bearing security, not tidiness: reflection happily resolves `__construct`, and invoking it re-runs the constructor on the container-shared instance, overwriting injected dependencies for the rest of the request — or the worker process on FrankenPHP/RoadRunner. Non-public methods would otherwise raise a raw `\Error` and surface as a 500.
- **Argument resolution reuses Symfony's controller machinery, including the kernel event.** `RpcRequestHandler::createArgumentResolutionRequest()` `duplicate()`s the incoming request from the `RequestStack` (6th constructor arg), overlays the RPC `params` onto both the `request` and `attributes` bags, forces the content type on the **headers**, and blanks the body. Each of those four steps has a non-obvious reason — a proxy's `HTTP_CONTENT_TYPE`, a nullable `#[MapRequestPayload]` resolving to a 400, and a measured 7x cost for the `initialize()`/server-array alternatives — commented at the call site; read them before editing, and re-measure before "cleaning up" the bound closure. The handler then calls `argument_resolver`, dispatches a `ControllerArgumentsEvent` (`KernelEvents::CONTROLLER_ARGUMENTS`) and re-reads the controller and arguments back off the event. That dispatch is load-bearing, not decorative: `RequestPayloadValueResolver` does its work in `onKernelControllerArguments`, so `#[MapRequestPayload]` only functions because of it — hence the `HttpKernelInterface` injected as the handler's 5th constructor arg (`config/services.php`). Any change to param handling must preserve all four pieces (duplication of the real request, dual request/attributes population, content type on the headers, event dispatch) rather than bypassing the resolver.
- **Result normalization is always on.** Every return value passes through the Symfony `serializer`. `#[RpcNormalizationContext([...])]` on the method supplies the normalization context, read reflectively via `ServiceDescriptor::getMethodAttribute()`.
- **Only `AbstractRpcException` subclasses become JSON-RPC errors.** Anything else rethrows and surfaces as a real 500. Reserved codes and their default messages are constants on `AbstractRpcException`; `RpcApplicationException` guards against user codes falling in the reserved `-32099..-32000` range.
- **`TypeError` disambiguation** (`RpcRequestHandler::isInvalidParamsException`) inspects the trace frame to tell "caller passed bad params" from "the method itself blew up", mapping only the former to `-32602`. Fragile by nature — keep its test coverage.
- **Batch responses accumulate by append, in no guaranteed order.** Entry errors are added while parsing, results as they are handled, so an error generally precedes the results regardless of its position in the request. This is intentional and spec-permitted. If you ever key responses by index, remember `array_map()` preserves keys given a single array, so a sparse keyed array encodes as a JSON object (`{"0":…}`) rather than an array — `RpcResponderTest::testBatchIsEncodedAsAJsonArrayNotAnObject` guards the array shape, and an assoc-array assertion cannot catch it, hence the raw-JSON assertion.
- **The event mirrors the incoming request's type, and the duplicate stays off the `RequestStack`.** The request type comes from `RequestStack::getParentRequest()`, which is `null` for the main request and for an empty stack alike. Do not hardcode `SUB_REQUEST` again: app subscribers on `kernel.controller_arguments` routinely early-return on `!$event->isMainRequest()`, and auth is often one of them, so hardcoding it silently disables authentication in consumer apps — this has already regressed once. None of Symfony's own listeners on that event guard on the request type, so the flag exists purely for user code. The duplicate is still not pushed onto the `RequestStack`: the real request is already current there and now equivalent in everything a listener reads, so pushing would only shadow it with a copy whose `attributes` carry RPC params.

## Testing

`tests/TestKernel.php` is a `MicroKernelTrait` kernel that registers FrameworkBundle and this bundle, imports `config/routes.php`, and — via its own `CompilerPassInterface` — registers `tests/Service/MockService.php` and `tests/Service/NeverInstantiatedService.php` with the `nanofelis_json_rpc` tag. `KERNEL_CLASS` is set in `phpunit.xml.dist`.

`NeverInstantiatedService` exists purely to prove laziness: its constructor flips a static flag, and tests assert the flag stays false when another service is called. `MockService` carries a defaulted constructor arg, a `__toString` and a private method so the exposure guards are covered end-to-end.

Add new exposable RPC methods to `MockService` when covering handler/resolver behaviour end-to-end (`tests/Action/RpcTest.php` uses `WebTestCase` + `KernelBrowser`); unit-level tests instantiate collaborators directly (`ServiceFinderTest`).

`SYMFONY_DEPRECATIONS_HELPER=max[self]=0` — deprecations triggered by this bundle's own code fail the suite.

Booting the test kernel makes Symfony write `config/reference.php` — auto-generated app-only scaffolding, not part of the bundle. It is gitignored and excluded from php-cs-fixer; leave both guards in place rather than formatting or committing the file.

## Versioning

`master` is the 2.x line (`dev-master` → `2.0-dev`). v2 dropped `AbstractRpcService::getServiceKey()` in favour of `#[JsonRpcService]`; see `docs/migration.md`. Docs are split: `docs/v2/index.md` (current), `docs/v1/index.md` (legacy). User-facing behaviour changes belong in `docs/v2/index.md` and `CHANGELOG.md`.

2.0.0 has not shipped yet. `CHANGELOG.md` carries a `## Unreleased` section above the still-undated `## [2.0.0] - Unreleased`; append to one of those rather than starting a new version heading.
