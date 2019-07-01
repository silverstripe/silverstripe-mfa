<?php declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use LeftAndMain;
use Config;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Extension\SiteConfigExtension;
use Date as DBDate;
use Member;
use SiteConfig;
use SS_Object;

/**
 * The EnforcementManager class is responsible for making decisions regarding multi factor authentication app flow,
 * e.g. "should we redirect to the MFA section", "can the user skip MFA registration" etc.
 */
class EnforcementManager extends SS_Object
{
    /**
     * Indicate how many MFA methods the user must authenticate with before they are considered logged in
     *
     * @config
     * @var int
     */
    private static $required_mfa_methods = 1;

    /**
     * If true, redirects to MFA will only provided when the current user has access to some part of the CMS or
     * administration area.
     *
     * @config
     * @var bool
     */
    private static $requires_admin_access = true;

    /**
     * Whether enforcement of MFA is enabled. If this is disabled, users will not be redirected to MFA registration
     * or verification on login flows.
     *
     * @config
     * @var bool
     */
    private static $enabled = true;

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
    public function canSkipMFA(Member $member): bool
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
     *  - Whether MFA is enabled
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
    public function shouldRedirectToMFA(Member $member): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        if ($this->config()->get('requires_admin_access') && !$this->hasAdminAccess($member)) {
            return false;
        }

        $methodRegistry = MethodRegistry::singleton();
        $methods = $methodRegistry->getMethods();
        // If there are no methods available excluding backup codes, do not redirect
        if (!count($methods) || (count($methods) === 1 && $methodRegistry->getBackupMethod() !== null)) {
            return false;
        }

        if ($member->RegisteredMFAMethods()->exists()) {
            return true;
        }

        if ($this->isMFARequired()) {
            return true;
        }

        if ($this->isGracePeriodInEffect()) {
            return true;
        }

        if (!$member->HasSkippedMFARegistration) {
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
    public function hasCompletedRegistration(Member $member): bool
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
    public function isMFARequired(): bool
    {
        /** @var SiteConfig&SiteConfigExtension $siteConfig */
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

    /**
     * Specifically determines whether the MFA Grace Period is currently active.
     *
     * @return bool
     */
    public function isGracePeriodInEffect(): bool
    {
        /** @var SiteConfig&SiteConfigExtension $siteConfig */
        $siteConfig = SiteConfig::current_site_config();

        $isRequired = $siteConfig->MFARequired;
        if (!$isRequired) {
            return false;
        }

        $gracePeriod = $siteConfig->MFAGracePeriodExpires;
        if (!$gracePeriod) {
            return false;
        }

        /** @var DBDate $gracePeriodDate */
        $gracePeriodDate = $siteConfig->dbObject('MFAGracePeriodExpires');
        if ($gracePeriodDate->InPast()) {
            return false;
        }

        return true;
    }

    /**
     * Decides whether the current user has access to any LeftAndMain controller, which indicates some level
     * of access to the CMS.
     *
     * See LeftAndMain::init().
     *
     * @param Member $member
     * @return bool
     */
    protected function hasAdminAccess(Member $member): bool
    {
        $leftAndMain = LeftAndMain::singleton();
        if ($leftAndMain->canView($member)) {
            return true;
        }

        // Look through all LeftAndMain subclasses to find if one permits the member to view
        $menu = $leftAndMain->MainMenu();
        foreach ($menu as $candidate) {
            if ($candidate->Link
                && $candidate->Link !== $leftAndMain->Link()
                && $candidate->MenuItem->controller
                && singleton($candidate->MenuItem->controller)->canView($member)
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function isEnabled(): bool
    {
        return (bool) $this->config()->get('enabled');
    }
}
