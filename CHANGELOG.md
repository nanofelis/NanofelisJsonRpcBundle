## [2.0.0] - 2025-XX-XX

### ⚠️ Breaking changes

- Removed support for `getServiceKey()` method.
- All RPC services must now use the `#[JsonRpcService('serviceKey')]` attribute.
- Introduced usage of PHP 8 attributes for service identification.
