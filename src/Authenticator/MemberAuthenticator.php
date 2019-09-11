<?php

namespace SilverStripe\MFA\Authenticator;

use Controller;
use MemberAuthenticator as BaseMemberAuthenticator;

class MemberAuthenticator extends BaseMemberAuthenticator
{
    public static function get_login_form(Controller $controller)
    {
        // Use the name of the parent form.
        return LoginForm::create($controller, 'LoginForm');
    }
}
