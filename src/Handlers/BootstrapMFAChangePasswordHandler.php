<?php

namespace Firesphere\BootstrapMFA\Handlers;

use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\ValidationException;
use SilverStripe\Security\MemberAuthenticator\ChangePasswordForm;
use SilverStripe\Security\MemberAuthenticator\ChangePasswordHandler;
use SilverStripe\Security\Security;

/**
 * Class BootstrapMFAChangePasswordHandler
 * @package Firesphere\BootstrapMFA\Handlers
 */
class BootstrapMFAChangePasswordHandler extends ChangePasswordHandler
{
    /**
     * @param array $data
     * @param ChangePasswordForm $form
     * @return HTTPResponse
     * @throws ValidationException
     */
    public function doChangePassword(array $data, $form)
    {
        $return = parent::doChangePassword($data, $form);
        $provider = Injector::inst()->get(BootstrapMFAProvider::class);
        $provider->setMember(Security::getCurrentUser());
        $provider->updateTokens();

        return $return;
    }
}
