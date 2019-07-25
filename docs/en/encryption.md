# Configuring encryption providers

By default this module uses [defuse/php-encryption](https://github.com/defuse/php-encryption) as its encryption adapter
for secret information that must be persisted to a data store, such as a TOTP secret.

You can add your own implementation if you would like to use something different, by implementing
`EncryptionAdapterInterface` and configuring your service class with Injector. The interface is deliberately simple,
and takes `encrypt()` and `decrypt()` methods with a payload and an encryption key argument.

```yaml
SilverStripe\Core\Injector\Injector:
  SilverStripe\MFA\Service\EncryptionAdapterInterface:
    class: App\MFA\ReallyStrongEncryptionAdapter
```

**Please note:** this is different from the `PasswordEncryptor` API provided by silverstripe/framework
because we need two-way encryption (as opposed to one-way hashing) for MFA.
