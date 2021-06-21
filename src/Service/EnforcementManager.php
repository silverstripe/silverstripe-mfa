<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use SilverStripe\Admin\LeftAndMain;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Extension\SiteConfigExtension;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * The EnforcementManager class is responsible for making decisions regarding multi-factor authentication app flow,
 * e.g. "should we redirect to the MFA section", "can the user skip MFA registration" etc.
 */
class EnforcementManager
{
    use Configurable;
    use Injectable;

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
     * Whether the provided member can skip the MFA registration process.
     *
     * This is determined by a combination of:
     *
     *  - Whether MFA is enabled and there are methods available for use
     *  - Whether the user has admin access (MFA is disabled by default for users that don't)
     *  - Whether MFA is required - @see EnforcementManager::isMFARequired()
     *  - Whether the user has registered MFA methods already
     *
     * @param Member&MemberExtension $member
     * @return bool
     */
    public function canSkipMFA(Member $member): bool
    {
        if (!$this->isEnabled()) {
            return true;
        }

        if ($this->config()->get('requires_admin_access') && !$this->hasAdminAccess($member)) {
            return true;
        }

        if ($this->isMFARequired()) {
            return false;
        }

        if ($member->RegisteredMFAMethods()->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Whether the authentication process should redirect the provided user to MFA registration or login.
     *
     * This is determined by a combination of:
     *
     *  - Whether MFA is enabled and there are methods available for use
     *  - Whether the user has admin access (MFA is disabled by default for users that don't)
     *  - Whether the user has existing MFA methods registered
     *  - Whether a grace period is in effect (we always redirect eligible users in this case)
     *  - Whether MFA is mandatory (without a grace period or after it has expired)
     *  - Whether the user has previously opted to skip the registration process
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

        if (!$this->isUserInMFAEnabledGroup($member) && !$this->hasCompletedRegistration($member)) {
            return false;
        }

        if ($member->RegisteredMFAMethods()->exists()) {
            return true;
        }

        if ($this->isGracePeriodInEffect()) {
            return true;
        }

        if ($this->isMFARequired()) {
            return true;
        }

        if (!$member->HasSkippedMFARegistration) {
            return true;
        }

        return false;
    }

    /**
     * Check if the provided member has registered the required MFA methods. This includes the default backup method
     * if configured, and at least one other method.
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
     * Whether MFA is required for eligible users. This takes into account whether a grace period is set and whether
     * we're currently inside the window for it.
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
     * Decides whether the provided user has access to any LeftAndMain controller, which indicates some level
     * of access to the CMS.
     *
     * @see LeftAndMain::init()
     * @param Member $member
     * @return bool
     */
    protected function hasAdminAccess(Member $member): bool
    {
        return Member::actAs($member, function () use ($member) {
            $leftAndMain = LeftAndMain::singleton();
            if ($leftAndMain->canView($member)) {
                return true;
            }

            // Look through all LeftAndMain subclasses to find if one permits the member to view
            $menu = $leftAndMain->MainMenu(false);
            foreach ($menu as $candidate) {
                if (
                    $candidate->Link
                    && $candidate->Link !== $leftAndMain->Link()
                    && $candidate->MenuItem->controller
                    && singleton($candidate->MenuItem->controller)->canView($member)
                ) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * MFA is enabled if:
     *
     * - The EnforcementManager::enabled configuration is set to true
     * - There is at least one non-backup method available to register
     *
     * @return bool
     */
    protected function isEnabled(): bool
    {
        if (!$this->config()->get('enabled')) {
            return false;
        }

        $methodRegistry = MethodRegistry::singleton();
        $methods = $methodRegistry->getMethods();
        // If there are no methods available excluding backup codes, do not redirect
        if (!count($methods) || (count($methods) === 1 && $methodRegistry->getBackupMethod() !== null)) {
            return false;
        }

        return true;
    }

    protected function isUserInMFAEnabledGroup(Member $member): bool
    {
        /** @var SiteConfig&SiteConfigExtension $siteConfig */
        $siteConfig = SiteConfig::current_site_config();

        $groups = $siteConfig->MFAGroupRestrictions();

        // If no groups are set in the Site Config MFAGroupRestrictions field, MFA is enabled for all users
        if ($groups->count() === 0) {
            return true;
        }
        foreach ($groups as $group) {
            if ($member->inGroup($group)) {
                return true;
            }
        }
        return false;
    }
}
