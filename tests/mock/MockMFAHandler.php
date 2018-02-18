<?php
namespace Firesphere\BootstrapMFA\Tests\Mock;

use Firesphere\BootstrapMFA\Handlers\MFALoginHandler;

class MockMFAHandler extends MFALoginHandler
{

    public function MFAForm()
    {
        return MFALoginForm::create();
    }
}