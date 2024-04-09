---
title: Debugging
---

# Debugging

The MFA module ships with a PSR-3 logger configured by default (a [Monolog](https://github.com/Seldaek/monolog/)
implementation), however no Monolog handlers are attached by default. To enable developer logging, you can
[attach a handler](https://docs.silverstripe.org/en/developer_guides/debugging/error_handling/#configuring-error-logging).
An example that will log to a `mfa.log` file in the project root:

```yml
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
// app/src/MFA/Handlers/MyCustomLoginHandler.php
namespace App\MFA\Handlers;

use Exception;
use Psr\Log\LoggerInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Store\StoreInterface;

class MyCustomLoginHandler implements LoginHandlerInterface
{
    private static $dependencies = [
        'Logger' => '%$' . LoggerInterface::class . '.mfa',
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
        } catch (Exception $ex) {
            $this->logger->debug('Something went wrong! ' . $ex->getMessage(), $ex->getTrace());
        }
    }
}
```
