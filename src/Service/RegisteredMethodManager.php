<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\Security\Member;

/**
 * The RegisteredMethodManager service class facilitates the communication of Members and RegisteredMethod instances
 * in a reusable singleton.
 */
class RegisteredMethodManager
{
    use Injectable;

    /**
     * Get an authentication method object matching the given method from the given member. Returns null if the given
     * method could not be found attached to the Member
     *
     * @param Member&MemberExtension $member
     * @param string $methodURLSegment The URL segment of the requested method
     * @return RegisteredMethod|null
     */
    public function getFromMember(Member $member, $methodURLSegment)
    {
        // Find the actual method registration data object from the member for the specified default authenticator
        foreach ($member->RegisteredMFAMethods() as $method) {
            if ($method->getMethod()->getURLSegment() === $methodURLSegment) {
                return $method;
            }
        }
    }

}
