<?php

namespace Firesphere\BootstrapMFA\Handlers;

use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\MemberAuthenticator\ChangePasswordHandler;

class MFAChangePasswordHandler extends ChangePasswordHandler
{
    public function doChangePassword(array $data, $form)
    {
        $return = parent::doChangePassword($data, $form);
        Injector::inst()->get(BootstrapMFAProvider::class)->updateTokens();
        return $return;
    }
}
