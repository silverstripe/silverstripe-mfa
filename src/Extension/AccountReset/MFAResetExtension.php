<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Extension\AccountReset;

use SilverStripe\Core\Extension;
use SilverStripe\MFA\Extension\MemberExtension as MemberExtension;
use SilverStripe\Security\Member;

/**
 * Handles removing a member's registered MFA methods during Account Reset. Also
 * resets the 'MFA Skipped' flag on the member so that they are prompted to
 * set up MFA again when they next log in.
 *
 * @extends Extension<SecurityExtension>
 */
class MFAResetExtension extends Extension
{
    /**
     * @param Member&MemberExtension $member
     */
    public function handleAccountReset(Member $member)
    {
        foreach ($member->RegisteredMFAMethods() as $method) {
            $method->delete();
        }

        $member->HasSkippedMFARegistration = false;
        $member->write();
    }
}
