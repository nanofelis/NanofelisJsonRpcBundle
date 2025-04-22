# Migration Guide: From v1.x to v2.x

This guide explains how to upgrade your project to NanofelisJsonRpcBundle **v2.x**.

---

## 🔥 Breaking Changes in v2

### 1. `getServiceKey()` has been removed

In v1, each RPC service class had to extend `AbstractRpcService` and implement a static `getServiceKey()` method:

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

In v2, this method has been removed. You must now use the native PHP 8 attribute #[JsonRpcService] and AbstractRpcService is no longer required.
use Nanofelis\JsonRpcBundle\Attribute\JsonRpcService;

```php
namespace App\RpcServices;

#[JsonRpcService('myService')]
class MyService
{
}
```
