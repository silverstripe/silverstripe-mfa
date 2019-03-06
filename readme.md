# MultiFactor Authentication for SilverStripe

[![Build Status](https://travis-ci.com/silverstripe/silverstripe-mfa.svg?branch=master)](https://travis-ci.com/silverstripe/silverstripe-mfa)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mfa/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mfa/?branch=master)
[![codecov](https://codecov.io/gh/silverstripe/silverstripe-mfa/branch/master/graph/badge.svg)](https://codecov.io/gh/silverstripe/silverstripe-mfa)

# Setup

After installing this module _and_ a supported factor method module (e.g. TOTP), the default member authenticator will be replaced with the MFA authenticator instead. This will provide no change in the steps taken to log in until an MFA Method has also been configured for the site:

```yml
SilverStripe\MFA\Service\MethodRegistry:
  methods:
    # register methods here
```
