[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Firesphere/silverstripe-bootstrapmfa/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Firesphere/silverstripe-bootstrapmfa/?branch=master)
[![Build Status](https://scrutinizer-ci.com/g/Firesphere/silverstripe-bootstrapmfa/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Firesphere/silverstripe-bootstrapmfa/build-status/master)
[![codecov](https://codecov.io/gh/Firesphere/silverstripe-bootstrapmfa/branch/master/graph/badge.svg)](https://codecov.io/gh/Firesphere/silverstripe-bootstrapmfa)

# MultiFactor Boostrap for SilverStripe

This module aims to help create an multi-factor authentication method for SilverStripe, by bootstrapping an unregistered authenticator with Backup tokens to have a basic start.

Current stage is a pretty basic implementation, but it should get you started pretty fast. For example of implementation, see https://github.com/Firesphere/silverstripe-yubiauth/tree/mfa-base

# Installation

`composer require firesphere/bootstrapmfa`

# Usage

First, after installation, create your MFA module. (or use an existing one that bolts on top of this one)

The module in itself does nothing but create the basic requirements for 2-factor token-based backup login. It does not provide active 2FA login methods.

Your Authenticator should extend `BootstrapMFAAuthenticator`
In the validation method, if the intended MFA method fails, you can fall back on the `parent::validate()` method.

This is the Bootstrap Authenticator, that will let people login with one-time text-tokens.

Your second-factor form needs to be it's own form, but has to have a field named `token`, which can fall back via the Bootstrapper..

This can be done on creation of the Member, via `BootstrapMFAProvider::updateTokens($member)`

# Configuration

```yaml

---
name: MFAAuthenticator
---
Firesphere\BootstrapMFA\BackupCode:
  token_limit: 15 # Default amount of tokens
Firesphere\BootstrapMFA\CodeGenerator:
  length: 6 # Length of the codes
  type: numeric # Type of the codes, numeric|mixed|characters
  case: mixed # Casing of the characters. upper|lower|mixed

```

In the CMS, a new set of tokens can be requested.

If a user resets its password, the login-screen will show the created new tokens (once)

Tokens are stored encrypted and can not be retrieved, only validated.

# Extending

This module is meant to be extended and not work on it's own. It only supplies fallback codes, like other MultiFactor authentication sites do.

When building an MFA module on top of this, a few things are required:
- Your MFALoginHandler extending BootstrapMFALoginHandler
- Your MFAProvider implementing the MFAProvider
- Your authenticator extending BootstrapMFAAuthenticator
    - Must call the validateBackupCode method to validate the MFA backup codes


An example of how to use this can be found at firesphere/silverstripe-yubiauth

A non-functional demo module is expected to be released soon~ish


# License
  
This module is published under BSD 3-clause license, although these are not in the actual classes, the license does apply:

http://www.opensource.org/licenses/BSD-2-Clause

Copyright (c) 2017-NOW(), Simon "Firesphere" Erkelens

All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

  Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
  Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.


# Did you read this entire readme? You rock!

Pictured below is a cow, just for you.
```

             /( ,,,,, )\
            _\,;;;;;;;,/_
         .-"; ;;;;;;;;; ;"-.
         '.__/`_ / \ _`\__.'
            | (')| |(') |
            | .--' '--. |
            |/ o     o \|
            |           |
           / \ _..=.._ / \
          /:. '._____.'   \
         ;::'    / \      .;
         |     _|_ _|_   ::|
       .-|     '==o=='    '|-.
      /  |  . /       \    |  \
      |  | ::|         |   | .|
      |  (  ')         (.  )::|
      |: |   |;  U U  ;|:: | `|
      |' |   | \ U U / |'  |  |
      ##V|   |_/`"""`\_|   |V##
         ##V##         ##V##
```
