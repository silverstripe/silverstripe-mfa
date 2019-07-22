<?php declare(strict_types=1);

namespace SilverStripe\MFA\Tests\Behat\Context;

use SilverStripe\CMS\Tests\Behaviour\LoginContext as CMSLoginContext;
use SilverStripe\BehatExtension\Context\LoginContext as BehatLoginContext;
use SilverStripe\MFA\Extension\SiteConfigExtension;
use SilverStripe\SiteConfig\SiteConfig;

if (!class_exists(CMSLoginContext::class) || !class_exists(BehatLoginContext::class)) {
    return;
}

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
    }

    /**
     * @Given multi-factor authentication is optional
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
