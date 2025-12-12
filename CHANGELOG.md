## [5.0.0] - 2025-XX-XX

### ⚠️ Breaking changes

- Removed support for `getServiceKey()` method.
- All RPC services must now use the `#[JsonRpcService('serviceKey')]` attribute.
- Introduced usage of PHP 8 attributes for service identification.

## [5.1.0] - 2025-12-15

### Added

- Added `#[RpcIsGranted]` attribute to protect RPC methods using Symfony Security.