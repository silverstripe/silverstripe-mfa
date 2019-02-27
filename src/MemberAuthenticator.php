<?php
namespace SilverStripe\MFA;

use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator as BaseMemberAuthenticator;

class MemberAuthenticator extends BaseMemberAuthenticator
{
    public function getLoginHandler($link)
    {
        return LoginHandler::create($link, $this);
    }
}
