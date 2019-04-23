<?php

namespace SilverStripe\MFA\Dev\State;

use Monolog\Handler\NullHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Dev\State\TestState;

/**
 * Clears any user defined loggers during unit test execution
 */
class LoggerTestState implements TestState
{
    public function setUp(SapphireTest $test)
    {
        /** @var Logger $logger */
        $logger = Injector::inst()->get(LoggerInterface::class . '.mfa');
        $logger->setHandlers([new NullHandler()]);
    }

    public function tearDown(SapphireTest $test)
    {
        // noop
    }

    public function setUpOnce($class)
    {
        // noop
    }

    public function tearDownOnce($class)
    {
        // noop
    }
}
