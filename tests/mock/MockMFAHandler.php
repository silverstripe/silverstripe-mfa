<?php

namespace Firesphere\BootstrapMFA\Tests\Mock;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Forms\MFALoginForm;
use Firesphere\BootstrapMFA\Handlers\MFALoginHandler;
use SilverStripe\Control\Controller;

class MockMFAHandler extends MFALoginHandler
{

    public function MFAForm()
    {
        return MFALoginForm::create(Controller::curr(), BootstrapMFAAuthenticator::class, __FUNCTION__);
    }
}