<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
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
     * @param MethodInterface $method
     * @return RegisteredMethod|null
     */
    public function getFromMember(Member $member, MethodInterface $method)
    {
        // Find the actual method registration data object from the member for the specified default authenticator
        foreach ($member->RegisteredMFAMethods() as $registeredMethod) {
            if ($registeredMethod->getMethod()->getURLSegment() === $method->getURLSegment()) {
                return $registeredMethod;
            }
        }
    }

    /**
     * Fetch an existing RegisteredMethod object from the Member or make a new one, and then ensure it's associated
     * to the given Member
     *
     * @param Member&MemberExtension $member
     * @param MethodInterface $method
     * @param mixed $data
     */
    public function registerForMember(Member $member, MethodInterface $method, $data = null)
    {
        if (empty($data)) {
            return;
        }

        $registeredMethod = $this->getFromMember($member, $method)
            ?: RegisteredMethod::create(['MethodClassName' => get_class($method)]);

        $registeredMethod->Data = json_encode($data);
        $registeredMethod->write();

        // Add it to the member
        $member->RegisteredMFAMethods()->add($registeredMethod);
    }
}
