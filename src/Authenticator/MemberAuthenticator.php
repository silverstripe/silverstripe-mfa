<?php

namespace SilverStripe\MFA\Authenticator;

use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator as BaseMemberAuthenticator;

class MemberAuthenticator extends BaseMemberAuthenticator
{
    public function getLoginHandler($link)
    {
        return LoginHandler::create($link, $this);
    }

    public function getChangePasswordHandler($link)
    {
        return ChangePasswordHandler::create($link, $this);
    }
}
