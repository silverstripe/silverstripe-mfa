---
title: Multi-factor Authentication (MFA)
---

# Multi-Factor Authentication (MFA)

## What is Multi-factor authentication?

Multi-factor authentication (MFA), often referred to as Two-factor
authentication (2FA), is an extra layer of security designed to be used
alongside your traditional username/email and password login. By adding another
verification step to the login process, you can prevent an unauthorised user
from accessing your account, even if they know your username/email and password.

Unlike your username/email and password, which is something only you know, MFA
verification asks you to provide something only you have - a physical device,
like your phone or a USB device. Some services can also verify something you
are - for example your fingerprint or face. For more information, see
[the CERT NZ guide](https://www.cert.govt.nz/individuals/guides/getting-started-with-cyber-security/two-factor-authentication/).

Two popular verification methods are supported by SilverStripe MFA:

### Authenticator apps (TOTP)

An authenticator app is installed on your phone which generates temporary
single-use passcodes needed for MFA verification. Each code is usable for only a
short period of time before a new one is automatically generated.

### Security keys (WebAuthn)

A security key is a physical device, such as a USB key, that is activated during
MFA verification. This may involve plugging the device into your computer or
bringing the key in range of a compatible device supporting wireless
communications (NFC). To use a security key with SilverStripe MFA, you must log
in using a supported browser over HTTPS
(see [Using security keys](user_manual/using_security_keys) for details).

[hint]
**Recovery codes**

Recovery codes are a backup verification method. In the event that you lose
access to your other verification methods, a recovery code can be used to
regain access to your account. A set of codes will be provided to you when you
first set up an MFA verification method on your account. You can only use each
of these codes once, and they should be stored somewhere safe.
[/hint]

## User manual

[CHILDREN Folder=01_User_manual]

## Administrator manual

[CHILDREN Folder=02_Administrator_manual]
