---
title: Multi-factor authentication (MFA)
---

# Multi-factor authentication (MFA)

This module provides bases classes for implementing multi-factor authentication (MFA) in Silverstripe CMS. You should also install one of the additional multi-factor authenticator modules:

- [silverstripe/totp-authenticator](https://github.com/silverstripe/silverstripe-totp-authenticator)
- [silverstripe/webauthn-authenticator](https://github.com/silverstripe/silverstripe-webauthn-authenticator)

This module provides two distinct processes for MFA; verification and registration. This module provides a decoupled
architecture where front-end and back-end are separate. Provided with the module is a React app that interfaces with
default endpoints added by this module.

## Installation

```bash
composer require silverstripe/mfa
```

## Setup

After installing this module *and* a supported factor method module (e.g. TOTP), the default member authenticator
will be replaced with the MFA authenticator instead. This will provide no change in the steps taken to log in until
an MFA Method has also been configured for the site. The TOTP and WebAuthn modules will configure themselves
automatically.

After installing the MFA module and having at least one method configured, MFA will automatically be enabled. By default
it will be optional (users can skip MFA registration). You can make it mandatory via the Settings tab in the admin area.

The MFA flow will only be applied to members with access to the CMS or administration area. See '[Broadening the scope of MFA](docs/en/broadening-the-scope-of-mfa.md)' for more detail.

You can disable MFA on an environment by setting a `BYPASS_MFA=1` environment variable,
or via YAML config - see [local development](docs/en/local-development) for details.

### Configuring custom methods

If you have built your own MFA method, you can register it with the [`MethodRegistry`](api:SilverStripe\MFA\Service\MethodRegistry) to enable it:

```yml
SilverStripe\MFA\Service\MethodRegistry:
  methods:
    - MyCustomMethod
    - Another\Custom\Method\Here
```

[CHILDREN includeFolders]
