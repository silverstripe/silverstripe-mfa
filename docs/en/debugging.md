# Debugging

The MFA module ships with a PSR-3 logger configured by default (a [Monolog](https://github.com/Seldaek/monolog/)
implementation), however no Monolog handlers are attached by default. To enable developer logging, you can
[attach a handler](https://docs.silverstripe.org/en/4/developer_guides/debugging/error_handling/#configuring-error-logging).
An example that will log to a `mfa.log` file in the project root:

```yaml
Injector:
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
            SS_Log::log('Something went wrong! ' . $ex->getMessage(), $ex->getTrace(), SS_Log::DEBUG);
        }
    }
}
```
