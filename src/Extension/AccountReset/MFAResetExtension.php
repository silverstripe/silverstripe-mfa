<?php declare(strict_types=1);

namespace SilverStripe\MFA\Extension\AccountReset;

use Extension;
use SilverStripe\MFA\Extension\MemberExtension as MemberExtension;
use Member;

/**
 * Handles removing a member's registered MFA methods during Account Reset. Also
 * resets the 'MFA Skipped' flag on the member so that they are prompted to
 * set up MFA again when they next log in.
 *
 * @package SilverStripe\MFA\Extension\AccountReset
 */
class MFAResetExtension implements AccountResetHandler
{
    /**
     * @param Member&MemberExtension $member
     */
    public function handleAccountReset(Member $member): void
    {
        foreach ($member->RegisteredMFAMethods() as $method) {
            $method->delete();
        }

        $member->HasSkippedMFARegistration = false;
        $member->write();
    }
}
