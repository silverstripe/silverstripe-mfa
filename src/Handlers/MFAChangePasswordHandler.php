<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 14-Jul-17
 * Time: 21:24
 */

namespace mfacodes\src\Handlers;

use Firesphere\BootstrapMFA\BootstrapMFAProvider;
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
