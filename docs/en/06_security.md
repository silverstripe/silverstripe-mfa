---
title: Security
---

# Security

## Login attempts

The MFA module makes use of the framework's [`LoginAttempt`](api:SilverStripe\Security\LoginAttempt) API to ensure that a user can only attempt to register
or verify a MFA method a certain number of times. Since it re-uses the core API, it also shares the maximum number
of attempts with login attempts themselves.

For example: if the maximum number of login attempts ([`Member.lock_out_after_incorrect_logins`](api:SilverStripe\Security\Member->lock_out_after_incorrect_logins)) is 5, and a user
incorrectly enters their password twice, correctly enters it once, then incorrectly enters a TOTP code three times,
they will be registered as locked out for a specified period of time ([`Member.lock_out_delay_mins`](api:SilverStripe\Security\Member->lock_out_delay_mins)). In this case,
the user will be shown a message when trying to verify their TOTP code similar to "Your account is temporarily locked.
Please try again later."

For more information on this, see [Secure Coding](https://docs.silverstripe.org/en/developer_guides/security/secure_coding/#other-options).

## Related links

- [MFA encryption providers](encryption.md)
- [silverstripe/security-extensions documentation](https://github.com/silverstripe/silverstripe-security-extensions)
