<?php

namespace Firesphere\BootstrapMFA\Providers;

use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\Security\Member;

interface MFAProvider
{
    /**
     * @param Member $member
     */
    public function setMember($member);

    /**
     * @param string $token
     * @return bool|BackupCode
     */
    public function fetchToken($token);
}
