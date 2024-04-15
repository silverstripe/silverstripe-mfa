---
title: Data store interfaces
---

# Data store interfaces

Since the MFA architecture is largely designed to be decoupled, we use a [`StoreInterface`](api:SilverStripe\MFA\Store\StoreInterface) implementation to retain
data between requests. The default implementation for this interface is [`SessionStore`](api:SilverStripe\MFA\Store\SessionStore) which stores data using the
Silverstripe CMS [`Session`](api:SilverStripe\Control\Session) API provided by silverstripe/framework.

If you need to use a different storage mechanism (e.g. Redis, DynamoDB etc) you can implement and configure your
own `StoreInterface`, and register it with Injector:

```yml
SilverStripe\Core\Injector\Injector:
  SilverStripe\MFA\Store\StoreInterface:
    class: App\MFA\RedisStoreInterface
```

> [!NOTE]
> The store should always be treated as a server side implementation. It's not a good idea to implement
> a client store e.g. cookies.

## Adjusting what goes into the store

By default, the entire [`HTTPRequest`](api:SilverStripe\Control\HTTPRequest) object is saved to the store during the multi-factor authentication process. We
exclude the `Password` field from the request by default, but if you need to exclude other fields, you can add an
extension, for example:

```php
// app/src/MFA/Extensions/MyLoginHandlerExtension.php
namespace App\MFA\Extensions;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Store\StoreInterface;

// Apply extension to SilverStripe\MFA\Authenticator\LoginHandler
class MyLoginHandlerExtension extends Extension
{
    public function onBeforeSaveRequestToStore(HTTPRequest $request, StoreInterface $store): void
    {
        $request->offsetUnset('MySecretField');
    }
}
```
