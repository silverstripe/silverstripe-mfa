# MultiFactor Authentication for SilverStripe

[![Build Status](https://travis-ci.com/silverstripe/silverstripe-mfa.svg?branch=master)](https://travis-ci.com/silverstripe/silverstripe-mfa)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mfa/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mfa/?branch=master)
[![codecov](https://codecov.io/gh/silverstripe/silverstripe-mfa/branch/master/graph/badge.svg)](https://codecov.io/gh/silverstripe/silverstripe-mfa)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

### With thanks to Simon `Firesphere` Erkelens

This module was based on pioneering work by Simon. It differs from the original implementation in its use of a pluggable
React UI + JSON API architecture, and its enhanced management UI within the CMS. You can find Simon's original module
[here](https://github.com/firesphere/silverstripe-bootstrapmfa).

## Requirements

* PHP ^7.1
* SilverStripe ^4.1
* defuse/php-encryption ^2.2 and OpenSSL PHP extension

## Installation

Install with Composer:

```
composer require silverstripe/mfa ^4.0
```

You should also install one of the additional multi-factor authenticator modules:

* [silverstripe/totp-authenticator](https://github.com/silverstripe/silverstripe-totp-authenticator)
* [silverstripe/webauthn-authenticator](https://github.com/silverstripe/silverstripe-webauthn-authenticator)

## Setup

After installing this module _and_ a supported factor method module (e.g. TOTP), the default member authenticator
will be replaced with the MFA authenticator instead. This will provide no change in the steps taken to log in until
an MFA Method has also been configured for the site. The TOTP and WebAuthn modules will configure themselves
automatically.

After installing the MFA module and having at least one method configured, MFA will automatically be enabled. By default
it will be optional (users can skip MFA registration). You can make it mandatory via the Settings tab in the admin area.

The MFA flow will only be applied to members with access to the CMS or administration area. See '[Broadening the scope of MFA](docs/en/broadening-the-scope-of-mfa.md)' for more detail.

### Configuring custom methods

If you have built your own MFA method, you can register it with the `MethodRegistry` to enable it:

```yaml
SilverStripe\MFA\Service\MethodRegistry:
  methods:
    - MyCustomMethod
    - Another\Custom\Method\Here
```

## Documentation

This module provides two distinct processes for MFA; verification and registration. This module provides a decoupled
architecture where front-end and back-end are separate. Provided with the module is a React app that interfaces with
default endpoints added by this module. Please refer to the docs for specific information about the included
functionality:

- [Debugging](docs/en/debugging.md)
- Creating new MFA methods
  - [Frontend](docs/en/creating-mfa-method-frontend.md)
  - [Backend](docs/en/creating-mfa-method-backend.md)
- [Local development](docs/en/local-development.md)
- [Encryption providers](docs/en/encryption.md)
- [Data store interfaces](docs/en/datastores.md)
- [Security](docs/en/security.md)
- [Integrating with other authenticators](docs/en/other-authenticators.md)

## Module development

When adding translatable content to front-end UIs in the MFA module, you must ensure that these translations are pushed
to Transifex. If this doesn't happen, they will be automatically removed in the next module released. See the
[translation docs](https://docs.silverstripe.org/en/4/contributing/translation_process/#javascript-translations)
for more information.

## License

See [license](LICENSE.md).

## Versioning

This library follows [Semver](http://semver.org). According to Semver, you will be able to upgrade to any minor or
patch version of this library without any breaking changes to the public API. Semver also requires that we clearly
define the public API for this library.

All methods, with `public` visibility, are part of the public API. All other methods are not part of the public API.
Where possible, we'll try to keep `protected` methods backwards-compatible in minor/patch versions, but if you're
overriding methods then please test your work before upgrading.

## Reporting issues

Please [create an issue](http://github.com/silverstripe/silverstripe-mfa/issues) for any bugs you've found, or
features you're missing.
