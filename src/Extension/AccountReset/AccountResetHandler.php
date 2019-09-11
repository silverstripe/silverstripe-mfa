<?php

namespace SilverStripe\MFA\Extension\AccountReset;

use Member;

/**
 * Designates that a class needs to apply changes during an Account Reset process
 *
 * @package SilverStripe\MFA\Extension\AccountReset
 */
interface AccountResetHandler
{
    /**
     * Perform any actions required to reset a Member's account
     *
     * @param Member $member
     * @return void
     */
    public function handleAccountReset(Member $member): void;
}
