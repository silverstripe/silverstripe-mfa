# MultiFactor Authentication for SilverStripe

[![Build Status](https://travis-ci.com/silverstripe/silverstripe-mfa.svg?branch=master)](https://travis-ci.com/silverstripe/silverstripe-mfa)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mfa/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/silverstripe/silverstripe-mfa/?branch=master)
[![codecov](https://codecov.io/gh/silverstripe/silverstripe-mfa/branch/master/graph/badge.svg)](https://codecov.io/gh/silverstripe/silverstripe-mfa)
[![SilverStripe supported module](https://img.shields.io/badge/silverstripe-supported-0071C4.svg)](https://www.silverstripe.org/software/addons/silverstripe-commercially-supported-module-list/)

## Requirements

* PHP ^7.1
* SilverStripe ^4.0
* defuse/php-encryption ^2.2 and OpenSSL PHP extension

## Installation

Install with Composer:

```
composer require silverstripe/mfa ^4.0
```

You should also install one of the additional multi factor authenticator modules:

* [silverstripe/totp-authenticator](https://github.com/silverstripe/silverstripe-totp-authenticator)
* More coming soon.

## Setup

After installing this module _and_ a supported factor method module (e.g. TOTP), the default member authenticator
will be replaced with the MFA authenticator instead. This will provide no change in the steps taken to log in until
an MFA Method has also been configured for the site:

```yaml
SilverStripe\MFA\Service\MethodRegistry:
  methods:
    - MyMethod
    - Another\Method\Here
```

After installing, an option in site configuration will enable MFA for users, which will automatically be added after 
login and to member profiles.

## Custom usage

This module provides two distinct processes for MFA; verification and registration. This module provides a decoupled 
architecture where front-end and back-end are separate. Provided with the module is a React app that interfaces with 
default endpoints added by this module. Please refer to the docs for specific information about the included 
functionality:

- [Front-end React components](docs/en/react-components.md)
- [Back-end controllers and traits](docs/en/controllers-and-handlers.md)

## Configuring encryption providers

By default this module uses defuse/php-encryption as its encryption adapter. You can add your own implementation if
you would like to use something different, by implementing `EncryptionAdapterInterface` and configuring your service
class with Injector. The interface is deliberately simple, and takes `encrypt()` and `decrypt()` methods with a
payload and an encryption key argument.

```yaml
SilverStripe\Core\Injector\Injector:
  SilverStripe\MFA\Service\EncryptionAdapterInterface:
    class: App\MFA\ReallyStrongEncryptionAdapter

```

## Data store interfaces

Since the MFA architecture is largely designed to be decoupled, we use a `StoreInterface` implementation to retain
data between requests. The default implementation for this interface is `SessionStore` which stores data in PHP
sessions. If you need to use a different storage mechanism (e.g. Redis, DynamoDB etc) you can implement and configure
your own `StoreInterface`, and register it with Injector:

```yaml
SilverStripe\Core\Injector\Injector:
  SilverStripe\MFA\Store\StoreInterface:
    class: App\MFA\RedisStoreInterface
```

Please note that the store should always be treated as a server side implementation. It's not a good idea to implement
a client store e.g. cookies.

## Debugging

The MFA module ships with a PSR-3 logger configured by default (a [Monolog](https://github.com/Seldaek/monolog/)
implementation), however no Monolog handlers are attached by default. To enable developer logging, you can
[attach a handler](https://docs.silverstripe.org/en/4/developer_guides/debugging/error_handling/#configuring-error-logging).
An example that will log to a `mfa.log` file in the project root:

```yaml
SilverStripe\Core\Injector\Injector:
  Psr\Log\LoggerInterface.mfa:
    calls:
      pushFileLogHandler: [ pushHandler, [ '%$MFAFileLogHandler' ] ]
  MFAFileLogHandler:
    class: Monolog\Handler\StreamHandler
    constructor:
      - '../mfa.log'
      - 'debug'
```

You can inject this logger into any MFA authenticator module, or custom app code, by using dependency injection:

```php
class MyCustomLoginHandler implements LoginHandlerInterface
{
    private static $dependencies = [
        'Logger' => '%$' . \Psr\Log\LoggerInterface::class . '.mfa',
    ];
    
    protected $logger;
    
    public function setLogger(LoggerInterface $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function start(StoreInterface $store, RegisteredMethod $method): array
    {
        try {
            $method->doSomething();
        } catch (\Exception $ex) {
            $this->logger->debug('Something went wrong! ' . $ex->getMessage(), $ex->getTrace());
        }
    }
}
```
