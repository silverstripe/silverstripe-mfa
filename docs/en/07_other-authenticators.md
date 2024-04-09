---
title: Integrating with other authenticators
---

# Integrating with other authenticators

If your project uses a non-standard authentication module, such as [`silverstripe/ldap`](https://github.com/silverstripe/silverstripe-ldap), you will
need to implement some customisations to connect the modules together. The following notes should serve as a guide
for parts of the code to be aware of, and things to do in order to achieve this.

For the purposes of comparisons in this document, we will use `silverstripe/ldap`'s authenticator.

## Concepts

### Configuration

The MFA module enables and sets itself as the `default` authenticator with config (see `config.yml`). You can leave this
in place, but change the injected class to a custom class in your project with Injector configuration. See further
down for a hypothetical example.

### Authenticator

The Authenticator entrypoint class in the MFA module is [`SilverStripe\MFA\Authenticator\MemberAuthenticator`](api:SilverStripe\MFA\Authenticator\MemberAuthenticator). This
class extends the default [`SilverStripe\Security\MemberAuthenticator`](api:SilverStripe\Security\MemberAuthenticator) class in order to override the default login
form with [`LoginForm`](api:SilverStripe\MFA\Authenticator\LoginForm), and the change password handler with [`ChangePasswordHandler`](api:SilverStripe\MFA\Authenticator\ChangePasswordHandler).

`silverstripe/ldap` does the same thing - it also configures itself to override the `default` authenticator. Since the
MFA replacement for the default authenticator has MFA logic added to it, and LDAP has the same with LDAP logic added,
you will need to reimplement it so that both MFA and LDAP apply their logic together.

In order to combine these two authenticators, you may choose to add your own `LDAPMFAAuthenticator` class and
configure that instead of either MFA or LDAP's authenticators. See further down for a hypothetical example.

### `LoginHandler`

The MFA [`LoginHandler`](api:SilverStripe\MFA\Authenticator\LoginHandler) class is the point where MFA flows are injected into core. In silverstripe/ldap, this
class performs the same function: to inject LDAP authentication logic into core. As above, in order to have both work
together you may choose to add your own `LDAPMFALoginHandler` class and configure that in your custom Authenticator.

This class would need to combine the logic from both `SilverStripe\LDAP\Forms\LDAPLoginHandler`
and `SilverStripe\MFA\Authenticator\LoginHandler` in order to function correctly for both cases.

### `ChangePasswordHandler`

Both the LDAP and MFA modules provide their own implementations of the `ChangePasswordHandler`, and in both cases
these are referenced from the `MemberAuthenticator` subclass of each module. Similarly to the `LoginForm` example
above, you will need to subclass and inject a custom implementation of this as well, combining both sets of logic.

Similarly to `LoginForm` above, in order to reduce duplication of code we recommend extending
`\SilverStripe\MFA\Authenticator\LoginHandler` and duplicating the contents of
`SilverStripe\LDAP\Authenticators\LDAPChangePasswordHandler` which is substantially smaller.

### `LoginForm` and `ChangePasswordForm`

The LDAP module overrides a couple of the default Form implementations: `LDAPLoginForm` and `LDAPChangePasswordForm`
form. The way that these classes are written likely indicates that there will not be any conflicts here with the
MFA module, which does not extend these classes from core.

## Examples

The following example classes should be enabled in your project with Injector configuration. For example:

```yml
SilverStripe\Core\Injector\Injector:
  SilverStripe\MFA\Authenticator\MemberAuthenticator:
    class: LDAPMFAAuthenticator
```

Note that this example overrides the default injection class for MemberAuthenticator, which will allow MFA's
configuration to register the method and set it as the default authenticator to continue. If you have [configured
the LDAP authenticator](https://github.com/silverstripe/silverstripe-ldap/blob/master/docs/en/developer.md#show-the-ldap-login-button-on-login-form)
you will want to remove this now - MFA configures itself automatically.

### A custom `MemberAuthenticator`

```php
// app/src/MFA/Authenticators/LDAPMFAMemberAuthenticator.php
namespace App\MFA\Authenticators;

use SilverStripe\LDAP\Authenticators\LDAPAuthenticator;
use SilverStripe\MFA\Authenticator\ChangePasswordHandler;
use SilverStripe\MFA\Authenticator\LoginHandler;

class LDAPMFAMemberAuthenticator extends LDAPAuthenticator
{
    public function getLoginHandler($link)
    {
        return LoginHandler::create($link, $this);
    }

    public function getChangePasswordHandler($link)
    {
        return ChangePasswordHandler::create($link, $this);
    }
}
```

In this example, we have copied the small amount of logic from the MFA module into this subclass, changed the parent
class from the core `MemberAuthenticator` to `LDAPAuthenticator`, and will change the injection class name with the
configuration above so it is used instead of MFA or LDAP.

### A custom `LoginHandler`

In this example, the logic from silverstripe/ldap is much smaller, so is preferable to duplicate while extending the
MFA `LoginHandler` which contains much more logic.

```php
// app/src/MFA/Handlers/LDAPMFALoginHandler.php
namespace App\MFAHandlers;

use SilverStripe\LDAP\Forms\LDAPLoginForm;
use SilverStripe\MFA\Authenticator\LoginHandler;

class LDAPMFALoginHandler extends LoginHandler
{
    private static $allowed_actions = ['LoginForm'];

    public function loginForm()
    {
        return LDAPLoginForm::create($this, get_class($this->authenticator), 'LoginForm');
    }
}
```

### A custom `ChangePasswordHandler`

As with the `LoginHandler` example above, the logic from silverstripe/ldap's `ChangePasswordHandler` is much smaller,
so is used for this example.

```php
// app/src/MFA/Handlers/LDAPMFAChangePasswordHandler.php
namespace App\MFA\Handlers;

use SilverStripe\LDAP\Forms\LDAPChangePasswordForm;
use SilverStripe\MFA\Authenticator\ChangePasswordHandler;

class LDAPMFAChangePasswordHandler extends ChangePasswordHandler
{
    private static $allowed_actions = [
        'changepassword',
        'changePasswordForm',
    ];

    public function changePasswordForm()
    {
        return LDAPChangePasswordForm::create($this, 'ChangePasswordForm');
    }
}
```
