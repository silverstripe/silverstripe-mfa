---
title: Creating a new MFA method - backend
---

# Creating a new MFA method - backend

## Method availability

If your method isn't available in some situations, and you can determine this via server-side state, you can provide
this information to the frontend via [`MethodInterface::isAvailable()`](api:SilverStripe\MFA\Method\MethodInterface::isAvailable()), for example:

```php
// app/src/MFA/Methods/MyMethod.php
namespace App\MFA\Methods;

class MyMethod implements MethodInterface
{
    public function isAvailable(): bool
    {
        return Injector::inst()->get(HTTPRequest::class)->getHeader('something') === 'example';
    }

    public function getUnavailableMessage(): string
    {
        return 'My silly example criteria was not fulfilled, so you cannot use me.';
    }
}
```

The results of both of these methods are automatically exposed to the MFA application schema when the registration /
verification UI is loaded, so no extra work is required to incorporate them.

If you need to determine the availability of your method via the frontend, see [Creating a new MFA method: Frontend](creating-mfa-method-frontend.md#method-availability)
