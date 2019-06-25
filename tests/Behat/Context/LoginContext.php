<?php declare(strict_types=1);

namespace SilverStripe\MFA\Tests\Behat\Context;

use SilverStripe\CMS\Tests\Behaviour\LoginContext as CMSLoginContext;
use SilverStripe\MFA\Extension\SiteConfigExtension;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Overridden from the CMS module to ensure that MFA can be handled during fixtured member generation
 */
class LoginContext extends CMSLoginContext
{
    public function iAmLoggedInWithPermissions($permCode)
    {
        // Set MFA to optional, perform login logic, then skip MFA
        $this->multiFactorAuthenticationIsOptional();
        parent::iAmLoggedInWithPermissions($permCode);

        // Wait for MFA to load
        $this->getMainContext()->getSession()
            ->wait(5000, 'document.getElementsByClassName("mfa-app-title").length === 1');

        $this->getMainContext()->pressButton('Setup later');
    }

    /**
     * @Given multi factor authentication is optional
     */
    public function multiFactorAuthenticationIsOptional()
    {
        /** @var SiteConfig&SiteConfigExtension $siteConfig */
        $siteConfig = SiteConfig::current_site_config();
        assertNotNull($siteConfig, 'Current SiteConfig record could not be found!');

        $siteConfig->MFARequired = false;
        $siteConfig->write();
    }

    /**
     * @When I select :option from the MFA settings
     */
    public function iSelectFromTheMfaSettings($option)
    {
        $value = $option === 'MFA is required for everyone' ? 1 : 0;
        $this->getMainContext()->selectOption('MFARequired', $value);
    }
}
