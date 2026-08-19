## Unreleased

### Fixed

- Run RPC methods through Symfony's standard controller argument lifecycle, including the `kernel.controller_arguments` event.

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
