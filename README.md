# MultiFactor Authentication for SilverStripe

[![CI](https://github.com/silverstripe/silverstripe-mfa/actions/workflows/ci.yml/badge.svg)](https://github.com/silverstripe/silverstripe-mfa/actions/workflows/ci.yml)
[![Silverstripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

## With thanks to Simon `Firesphere` Erkelens

This module was based on pioneering work by Simon. It differs from the original implementation in its use of a pluggable
React UI + JSON API architecture, and its enhanced management UI within the CMS. You can find Simon's original module
[here](https://github.com/firesphere/silverstripe-bootstrapmfa).

## Installation

```bash
composer require silverstripe/mfa
```

## Documentation

Read the [documentation](docs/en/index.md).

## Module development

When adding translatable content to front-end UIs in the MFA module, you must ensure that these translations are pushed
to Transifex. If this doesn't happen, they will be automatically removed in the next module released. See the
[translation docs](https://docs.silverstripe.org/en/contributing/translation_process/#javascript-translations)
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
