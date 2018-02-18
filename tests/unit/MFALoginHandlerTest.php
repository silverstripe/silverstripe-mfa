<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Forms\MFALoginForm;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;

class MFALoginHandlerTest extends SapphireTest
{


    public function testValidate()
    {
        $controller = Controller::curr();
        $controller->getRequest()->getSession()->set('tokens', '12345678');
        /** @var MFALoginForm $form */
        $form = Injector::inst()->createWithArgs(MFALoginForm::class, [$controller, BootstrapMFAAuthenticator::class, 'test']);

        $this->assertTrue(true);
    }
}