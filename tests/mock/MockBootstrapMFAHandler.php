<?php

namespace Firesphere\BootstrapMFA\Tests\Mock;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Forms\BootstrapMFALoginForm;
use Firesphere\BootstrapMFA\Handlers\BootstrapMFALoginHandler;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\TestOnly;

class MockBootstrapMFAHandler extends BootstrapMFALoginHandler implements TestOnly
{
    /**
     * @return static
     */
    public function MFAForm()
    {
        return BootstrapMFALoginForm::create(Controller::curr(), BootstrapMFAAuthenticator::class, __FUNCTION__);
    }
}
