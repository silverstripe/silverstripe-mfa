# MultiFactor Authentication for SilverStripe

[![Build Status](https://travis-ci.com/silverstripe/silverstripe-mfa.svg?branch=master)](https://travis-ci.com/silverstripe/silverstripe-mfa)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mfa/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mfa/?branch=master)
[![codecov](https://codecov.io/gh/silverstripe/silverstripe-mfa/branch/master/graph/badge.svg)](https://codecov.io/gh/silverstripe/silverstripe-mfa)

# Setup

After installing this module _and_ a supported factor method module (e.g. TOTP), you will need to use configuration to replace the default member authenticator with the MFA authenticator instead.

E.g.

```yml
---
Name: mysitemfa
After: '#coresecurity'
---
SilverStripe\Core\Injector\Injector:
  SilverStripe\Security\Security:
    properties:
      Authenticators:
        default: %$SilverStripe\MFA\Authenticator\MemberAuthenticator
```
