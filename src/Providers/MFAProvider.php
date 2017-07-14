<?php

namespace Firesphere\BootstrapMFA;

use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Member;

interface MFAProvider
{
    /**
     * @param Member $member
     */
    public function setMember($member);

    /**
     * @param string $token
     * @param null|ValidationResult $result
     * @return bool|Member
     */
    public function verifyToken($token, &$result = null);
}
