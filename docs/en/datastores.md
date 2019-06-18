# Data store interfaces

Since the MFA architecture is largely designed to be decoupled, we use a `StoreInterface` implementation to retain
data between requests. The default implementation for this interface is `SessionStore` which stores data in PHP
sessions. If you need to use a different storage mechanism (e.g. Redis, DynamoDB etc) you can implement and configure
your own `StoreInterface`, and register it with Injector:

```yaml
SilverStripe\Core\Injector\Injector:
  SilverStripe\MFA\Store\StoreInterface:
    class: App\MFA\RedisStoreInterface
```

Please note that the store should always be treated as a server side implementation. It's not a good idea to implement
a client store e.g. cookies.
