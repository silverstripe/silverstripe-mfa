<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * The EnforcementManager class is responsible for making decisions regarding multi factor authentication app flow,
 * e.g. "is MFA required", "should we redirect to the MFA section", "can the user skip MFA registration" etc.
 */
class EnforcementManager
{
    use Injectable;

    /**
     * Whether the current member can skip the multi factor authentication registration process.
     *
     * This is determined by a combination of:
     *  - Whether MFA is required or optional
     *  - If MFA is required, whether there is a grace period
     *  - If MFA is required and there is a grace period, whether we're currently within that timeframe
     *
     * @param Member&MemberExtension $member
     * @return bool
     */
    public function canSkipMFA(Member $member)
    {
        if ($this->isMFARequired()) {
            return false;
        }

        // If they've already registered MFA methods we will not allow them to skip the authentication process
        $registeredMethods = $member->RegisteredMFAMethods();
        if ($registeredMethods->exists()) {
            return false;
        }

        // MFA is optional, or is required but might be within a grace period (see isMFARequired)
        return true;
    }

    /**
     * Whether the authentication process should redirect the user to multi factor authentication registration or
     * login.
     *
     * This is determined by a combination of:
     *  - Whether MFA is required or optional
     *  - Whether the user has registered MFA methods already
     *  - If the user doesn't have any registered MFA methods already, and MFA is optional, whether the user has opted
     *    to skip the registration process
     *
     * Note that in determining this, we ignore whether or not MFA is enabled for the site in general.
     *
     * @param Member&MemberExtension $member
     * @return bool
     */
    public function shouldRedirectToMFA(Member $member)
    {
        $isRequired = $this->isMFARequired();
        if ($isRequired) {
            return true;
        }

        $hasSkipped = $member->HasSkippedMFARegistration;
        if (!$hasSkipped) {
            return true;
        }

        return false;
    }

    /**
     * Check if the provided member has registered the required MFA methods. This includes a "back-up" method set in
     * configuration plus at least one other method.
     * Note that this method returns true if there is no backup method registered (and they have one other method
     *
     * @param Member&MemberExtension $member
     * @return bool
     */
    public function hasFullyRegisteredMFA(Member $member)
    {
        $methodCount = $member->RegisteredMFAMethods()->count();

        $backupMethod = Config::inst()->get(MethodRegistry::class, 'default_backup_method');
        if (!$backupMethod) {
            // Ensure they have at least one method
            return $methodCount > 0;
        }

        // Ensure they have the required backup method and at least 2 methods (the backup method plus one other)
        return ((bool) $member->RegisteredMFAMethods()->find('MethodClassName', $backupMethod)) && $methodCount > 1;
    }

    /**
     * Whether multi factor authentication is required for site members. This also takes into account whether a
     * grace period is set and whether we're currently inside the window for it.
     *
     * Note that in determining this, we ignore whether or not MFA is enabled for the site in general.
     *
     * @return bool
     */
    public function isMFARequired()
    {
        $siteConfig = SiteConfig::current_site_config();

        $isRequired = $siteConfig->MFARequired;
        if (!$isRequired) {
            return false;
        }

        $gracePeriod = $siteConfig->MFAGracePeriodExpires;
        if ($isRequired && !$gracePeriod) {
            return true;
        }

        /** @var DBDate $gracePeriodDate */
        $gracePeriodDate = $siteConfig->dbObject('MFAGracePeriodExpires');
        if ($isRequired && $gracePeriodDate->InPast()) {
            return true;
        }

        // MFA is required, a grace period is set, and it's in the future
        return false;
    }
}
