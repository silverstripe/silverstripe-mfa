<?php

namespace SilverStripe\MFA\Dev\State;

use Monolog\Handler\NullHandler; // Not present in SS3
use Monolog\Logger; // Not present in SS3
use Psr\Log\LoggerInterface; // Not present in SS3
use Injector;
use SapphireTest;
use SilverStripe\Dev\State\TestState; // Not present in SS3

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
