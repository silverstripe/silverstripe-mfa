# Integrating with other authenticators

**This version relates to SilverStripe 3.x configuration.**

If your project uses a non-standard authentication module, such as silverstripe/activedirectory, you will
need to implement some customisations to connect the modules together. The following notes should serve as a guide
for parts of the code to be aware of, and things to do in order to achieve this.

For the purposes of comparisons in this document, we will use silverstripe/activedirectory's LDAP authenticator.

## Concepts

### Configuration

The MFA module enables and sets itself as the default authenticator with config (see `config.yml`). You can leave this
in place, but change the injected class to a custom class in your project with Injector configuration. See further
down for a hypothetical example.

### Authenticator

The Authenticator entrypoint class in the MFA module is `SilverStripe\MFA\Authenticator\MemberAuthenticator`. This
class extends the default `MemberAuthenticator` class in order to override the default login form with
`SilverStripe\MFA\Authenticator\LoginForm`.

silverstripe/activedirectory does essentially the same thing - it recommends that you add the LDAP authenticator,
and remove the default authenticator. Removing the default authenticator will also remove the MFA logic applied to it,
so you will need to reimplement it in a way that is applied to LDAP as well.

The `LDAPAuthenticator` has important business logic in it. In order to combine these two authenticators, you may
choose to add your own `LDAPMFAAuthenticator` class and configure that instead of either MFA or LDAP's
authenticators. See further down for a hypothetical example.

### LoginForm

The MFA `LoginForm` class is the point where MFA flows are injected into core. In silverstripe/activedirectory, this
class performs the same function: to inject LDAP authentication logic into core. As above, in order to have both work
together you may choose to add your own `LDAPMFALoginForm` class and configure that in your custom Authenticator.

This class would need to combine the logic from both `LDAPLoginForm` and `SilverStripe\MFA\Authenticator\LoginForm` in
order to function correctly for both cases. See further down for a hypothetical example.

### ChangePasswordForm

The silverstripe/activedirectory module replaces the default `Security` controller with its own `LDAPSecurityController`
in order to modify some of the logic around changing passwords.

MFA's logic for changing passwords is applied via the `SilverStripe\MFA\Extension\ChangePasswordExtension` which is
applied to `Security`, so should continue to work with LDAP's overridden controller as well.

## Examples

The following example classes should be enabled in your project with Injector configuration. For example:

```yaml
Injector:
  SilverStripe\MFA\Authenticator\MemberAuthenticator:
    class: LDAPMFAAuthenticator
```

Note that this example overrides the default injection class for MemberAuthenticator, which will allow MFA's
configuration to register the method and set it as the default authenticator to continue. If you have calls to
`Authenticator::register_authenticator()` etc in your `_config.php` files, remove these now.

### A custom MemberAuthenticator

```php
class LDAPMFAAuthenticator extends LDAPAuthenticator
{
    public static function get_login_form(Controller $controller)
    {
        return LDAPMFALoginForm::create($controller, 'LoginForm');
    }
}
```

### A custom LoginForm

```php
class LDAPMFALoginForm extends \SilverStripe\MFA\Authenticator\LoginForm
{
    // copy everything from the LDAPLoginForm into here, since we'll be skipping that class now
}
```

**Note:** since both MFA and LDAP's LoginForm classes have a substantial amount of logic, the comment above should
suffice as a replacement for a copy-and-paste into this example.
