<?php

namespace SilverStripe\MFA\Tests\Service;

use Config;
use SapphireTest;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SS_Datetime as DBDatetime;
use Member;
use SiteConfig;

class EnforcementManagerTest extends SapphireTest
{
    protected static $fixture_file = 'EnforcementManagerTest.yml';

    public function setUp()
    {
        parent::setUp();

        DBDatetime::set_mock_now('2019-01-25 12:00:00');

        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(MethodRegistry::class, 'methods', [
            BasicMathMethod::class,
        ]);

        Config::inst()->update(EnforcementManager::class, 'requires_admin_access', true);
    }

    public function testUserWithoutCMSAccessCanSkipWhenCMSAccessIsRequired()
    {
        $this->setSiteConfig(['MFARequired' => true]);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sammy_smith');
        $this->assertTrue(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testUserWithoutCMSAccessCannotSkipWhenCMSAccessIsNotRequired()
    {
        Config::nest();

        $this->setSiteConfig(['MFARequired' => true]);
        Config::inst()->update(EnforcementManager::class, 'requires_admin_access', false);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sammy_smith');
        $this->assertFalse(EnforcementManager::create()->canSkipMFA($member));

        Config::unnest();
    }

    public function testCannotSkipWhenMFAIsRequiredWithNoGracePeriod()
    {
        $this->setSiteConfig(['MFARequired' => true]);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'reports_user');
        $this->assertFalse(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testCanSkipWhenMFAIsRequiredWithGracePeriodExpiringInFuture()
    {
        $this->setSiteConfig(['MFARequired' => true, 'MFAGracePeriodExpires' => '2019-01-30']);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'reports_user');
        $this->assertTrue(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testCannotSkipWhenMFAIsRequiredWithGracePeriodExpiringInPast()
    {
        $this->setSiteConfig(['MFARequired' => true, 'MFAGracePeriodExpires' => '2018-12-25']);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'reports_user');
        $this->assertFalse(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testCannotSkipWhenMemberHasRegisteredAuthenticationMethodsSetUp()
    {
        $this->setSiteConfig(['MFARequired' => false]);
        // Sally has "backup codes" as a registered authentication method already
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->logIn();

        $this->assertFalse(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testCanSkipWhenMFAIsOptional()
    {
        $this->setSiteConfig(['MFARequired' => false]);
        // Anonymous admin user
        $memberId = $this->logInWithPermission();
        /** @var Member $member */
        $member = Member::get()->byID($memberId);

        $this->assertTrue(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testShouldNotRedirectToMFAWhenUserDoesNotHaveCMSAccess()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sammy_smith');
        $member->logIn();
        $this->assertFalse(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldRedirectToMFAWhenUserDoesNotHaveCMSAccessButTheCheckIsDisabledWithConfig()
    {
        Config::nest();
        Config::inst()->update(EnforcementManager::class, 'requires_admin_access', false);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sammy_smith');
        $member->logIn();
        $this->assertTrue(EnforcementManager::create()->shouldRedirectToMFA($member));
        Config::unnest();
    }

    public function testShouldRedirectToMFAWhenUserHasAccessToReportsOnly()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'reports_user');
        $member->logIn();
        $this->assertTrue(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldRedirectToMFAForContentAuthors()
    {
        $memberID = $this->logInWithPermission('CMS_ACCESS_CMSMain');
        /** @var Member $member */
        $member = Member::get()->byID($memberID);
        $this->assertTrue(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldRedirectToMFAWhenUserHasRegisteredMFAMethod()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $shouldRedirect = EnforcementManager::create()->shouldRedirectToMFA($member);
        $this->assertTrue($shouldRedirect);
    }

    public function testShouldRedirectToMFAWhenMFAIsRequired()
    {
        $this->setSiteConfig(['MFARequired' => true]);
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->logIn();

        $this->assertTrue(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldRedirectToMFAWhenMFAIsRequiredWithGracePeriodExpiringInFuture()
    {
        $this->setSiteConfig(['MFARequired' => false, 'MFAGracePeriodExpires' => '2019-01-30']);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sammy_smith');
        $member->HasSkippedMFARegistration = true;
        $member->write();
        $this->logInAs($member);

        $this->assertFalse(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldRedirectToMFAWhenMFAIsRequiredWithGracePeriodExpiringInPast()
    {
        $this->setSiteConfig(['MFARequired' => false, 'MFAGracePeriodExpires' => '2018-12-25']);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sammy_smith');
        $member->HasSkippedMFARegistration = true;
        $member->write();
        $this->logInAs($member);

        $this->assertFalse(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldRedirectToMFAWhenMFAIsOptionalAndHasNotBeenSkipped()
    {
        $this->setSiteConfig(['MFARequired' => false]);

        /** @var Member|MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->HasSkippedMFARegistration = false;
        $member->write();
        $member->logIn();

        $this->assertTrue(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldNotRedirectToMFAWhenMFAIsOptionalAndHasBeenSkipped()
    {
        $this->setSiteConfig(['MFARequired' => false]);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sammy_smith');
        $member->HasSkippedMFARegistration = true;
        $member->write();
        $member->logIn();

        $this->assertFalse(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldNotRedirectToMFAWhenConfigIsDisabled()
    {
        Config::inst()->update(EnforcementManager::class, 'enabled', false);
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $shouldRedirect = EnforcementManager::create()->shouldRedirectToMFA($member);
        $this->assertFalse($shouldRedirect);
    }

    public function testShouldNotRedirectToMFAWhenNoMethodsAreRegisteredInTheSystem()
    {
        $this->setSiteConfig(['MFARequired' => true]);

        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(MethodRegistry::class, 'methods', []);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->logIn();

        $this->assertFalse(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    /**
     * Helper method for changing the current SiteConfig values
     *
     * @param array $data
     */
    protected function setSiteConfig(array $data)
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->update($data);
        $siteConfig->write();
    }
}
