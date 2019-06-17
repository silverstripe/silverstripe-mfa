<?php

namespace SilverStripe\MFA\Tests\Service;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

class EnforcementManagerTest extends SapphireTest
{
    protected static $fixture_file = 'EnforcementManagerTest.yml';

    protected function setUp()
    {
        parent::setUp();

        DBDatetime::set_mock_now('2019-01-25 12:00:00');

        MethodRegistry::config()->set('methods', [
            BasicMathMethod::class,
        ]);
    }

    public function testCannotSkipWhenMFAIsRequiredWithNoGracePeriod()
    {
        $this->setSiteConfig(['MFARequired' => true]);

        $member = new Member();
        $this->assertFalse(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testCanSkipWhenMFAIsRequiredWithGracePeriodExpiringInFuture()
    {
        $this->setSiteConfig(['MFARequired' => true, 'MFAGracePeriodExpires' => '2019-01-30']);

        $member = new Member();
        $this->assertTrue(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testCannotSkipWhenMFAIsRequiredWithGracePeriodExpiringInPast()
    {
        $this->setSiteConfig(['MFARequired' => true, 'MFAGracePeriodExpires' => '2018-12-25']);

        $member = new Member();
        $this->assertFalse(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testCannotSkipWhenMemberHasRegisteredAuthenticationMethodsSetUp()
    {
        $this->setSiteConfig(['MFARequired' => false]);
        // Sally has "backup codes" as a registered authentication method already
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->logInAs($member);

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

    public function testShouldRedirectToMFAWhenUserHasRegisteredMFAMethod()
    {
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $shouldRedirect = EnforcementManager::create()->shouldRedirectToMFA($member);
        $this->assertTrue($shouldRedirect);
    }

    public function testShouldRedirectToMFAWhenMFAIsRequired()
    {
        $this->setSiteConfig(['MFARequired' => true]);
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->logInAs($member);

        $this->assertTrue(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldRedirectToMFAWhenMFAIsOptionalAndHasNotBeenSkipped()
    {
        $this->setSiteConfig(['MFARequired' => false]);

        /** @var Member|MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->HasSkippedMFARegistration = false;
        $member->write();
        $this->logInAs($member);

        $this->assertTrue(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testShouldNotRedirectToMFAWhenMFAIsOptionalAndHasBeenSkipped()
    {
        $this->setSiteConfig(['MFARequired' => false]);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sammy_smith');
        $member->HasSkippedMFARegistration = true;
        $member->write();
        $this->logInAs($member);

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
