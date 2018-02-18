<?php

namespace Firesphere\BootstrapMFA\Handlers;

use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\MemberAuthenticator\ChangePasswordHandler;
use SilverStripe\Security\Security;

class MFAChangePasswordHandler extends ChangePasswordHandler
{
    public function doChangePassword(array $data, $form)
    {
        $return = parent::doChangePassword($data, $form);
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->setMember(Security::getCurrentUser());
        $provider->updateTokens();
        return $return;
    }
}
