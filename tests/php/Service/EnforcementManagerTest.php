<?php

namespace SilverStripe\MFA\Tests\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

class EnforcementManagerTest extends SapphireTest
{
    protected static $fixture_file = 'EnforcementManagerTest.yml';

    protected function setUp(): void
    {
        parent::setUp();

        DBDatetime::set_mock_now('2019-01-25 12:00:00');

        MethodRegistry::config()->set('methods', [
            BasicMathMethod::class,
        ]);

        EnforcementManager::config()->set('requires_admin_access', true);
        EnforcementManager::config()->set('enabled', true);
    }

    public function provideCanSkipMFA()
    {
        $scenarios = [
            // User with registered MFA option can _never_ skip MFA
            'already registered, optional for all' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sally_smith',
                'expected' => false,
            ],
            'already registered, required for all' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sally_smith',
                'expected' => false,
            ],
            'already registered, in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sally_smith',
                'expected' => false,
            ],
            'already registered, in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sally_smith',
                'expected' => false,
            ],
            'already registered, NOT in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'sally_smith',
                'expected' => false,
            ],
            'already registered, NOT in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'sally_smith',
                'expected' => false,
            ],
            // User without registered MFA option can skip unless they're not in a specified group
            'not registered, optional for all' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'john_smith',
                'expected' => true,
            ],
            'not registered, required for all' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'john_smith',
                'expected' => false,
            ],
            'not registered, in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'john_smith',
                'expected' => true,
            ],
            'not registered, in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'john_smith',
                'expected' => false,
            ],
            'not registered, NOT in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'john_smith',
                'expected' => true,
            ],
            'not registered, NOT in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'john_smith',
                'expected' => true,
            ],
            // Always skip if user has no CMS access
            // Note that this is altered by the "requires_admin_access" config, which is tested separately.
            'no cms access, optional for all' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'no cms access, required for all' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'no cms access, not in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'no cms access, not in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'no cms access, in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['nocmsgroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'no cms access, in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['nocmsgroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
        ];
        // Add scenarios with past and future expiry dates
        // See the setUp method, which sets the current datetime to "2019-01-25 12:00:00"
        foreach ($scenarios as $name => $scenario) {
            // Past expiry dates
            $pastScenario = $scenario;
            $pastScenario['mfaGracePeriodExpires'] = '2018-12-25';
            $scenarios[$name . ' with past expiry'] = $pastScenario;
            // Future expiry dates
            $futureScenario = $scenario;
            $futureScenario['mfaGracePeriodExpires'] = '2019-01-30';
            // Members who haven't registered MFA yet can skip if there's a currently-active grace period
            if ($futureScenario['memberFixture'] === 'john_smith') {
                $futureScenario['expected'] = true;
            }
            $scenarios[$name . ' with future expiry'] = $futureScenario;
        }
        return $scenarios;
    }

    /**
     * @dataProvider provideCanSkipMFA
     */
    public function testCanSkipMFA(
        bool $mfaRequired,
        string $mfaAppliesTo,
        ?string $mfaGracePeriodExpires,
        array $requiredGroupFixtures,
        string $memberFixture,
        bool $expected
    ) {
        $config = SiteConfig::current_site_config();
        foreach ($requiredGroupFixtures as $fixtureName) {
            $group = $this->objFromFixture(Group::class, $fixtureName);
            $config->MFAGroupRestrictions()->add($group);
        }
        $siteConfigUpdate = ['MFARequired' => $mfaRequired, 'MFAAppliesTo' => $mfaAppliesTo];
        if ($mfaGracePeriodExpires) {
            $siteConfigUpdate['MFAGracePeriodExpires'] = $mfaGracePeriodExpires;
        }
        $this->setSiteConfig($siteConfigUpdate);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, $memberFixture);
        $this->logInAs($member);

        $this->assertSame($expected, EnforcementManager::create()->canSkipMFA($member));
    }

    public function provideCanSkipMFAWithoutCMSAccess()
    {
        return [
            'optional, applies to everyone' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'required, applies to everyone' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
            'optional, not in group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'required, not in group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'optional, in group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'requiredGroupFixtures' => ['nocmsgroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'required, in group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'requiredGroupFixtures' => ['nocmsgroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider provideCanSkipMFAWithoutCMSAccess
     */
    public function testCanSkipMFAWithoutCMSAccess(
        bool $mfaRequired,
        string $mfaAppliesTo,
        array $requiredGroupFixtures,
        string $memberFixture,
        bool $expected
    ) {
        EnforcementManager::config()->set('requires_admin_access', false);
        $this->testCanSkipMFA($mfaRequired, $mfaAppliesTo, null, $requiredGroupFixtures, $memberFixture, $expected);
    }

    public function testCanSkipWhenMFAIsDisabled()
    {
        $this->setSiteConfig(['MFARequired' => true]);
        EnforcementManager::config()->set('enabled', false);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->assertTrue(EnforcementManager::create()->canSkipMFA($member));
    }

    public function testCanSkipWhenNoMethodsAreAvailable()
    {
        $this->setSiteConfig(['MFARequired' => true]);
        MethodRegistry::config()->set('methods', null);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->assertTrue(EnforcementManager::create()->canSkipMFA($member));
    }

    public function provideShouldRedirectToMFA()
    {
        $scenarios = [
            // User with registered MFA option should _always_ redirect to MFA
            'already registered, optional for all' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sally_smith',
                'expected' => true,
            ],
            'already registered, required for all' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sally_smith',
                'expected' => true,
            ],
            'already registered, in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sally_smith',
                'expected' => true,
            ],
            'already registered, in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sally_smith',
                'expected' => true,
            ],
            'already registered, NOT in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'sally_smith',
                'expected' => true,
            ],
            'already registered, NOT in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'sally_smith',
                'expected' => true,
            ],
            // User without registered MFA option should only redirect if rules apply to them
            'not registered, optional for all' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'john_smith',
                'expected' => true,
            ],
            'not registered, required for all' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'john_smith',
                'expected' => true,
            ],
            'not registered, in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'john_smith',
                'expected' => true,
            ],
            'not registered, in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'john_smith',
                'expected' => true,
            ],
            'not registered, NOT in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'john_smith',
                'expected' => false,
            ],
            'not registered, NOT in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'john_smith',
                'expected' => false,
            ],
            // User who has skipped MFA registration has slightly different behaviour
            'skipped registration, optional for all' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sully_smith',
                'expected' => false,
            ],
            'skipped registration, required for all' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sully_smith',
                'expected' => true,
            ],
            'skipped registration, in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'sully_smith',
                'expected' => false,
            ],
            'skipped registration, in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['reportsgroup'],
                'memberFixture' => 'sully_smith',
                'expected' => true,
            ],
            'skipped registration, NOT in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sully_smith',
                'expected' => false,
            ],
            'skipped registration, NOT in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sully_smith',
                'expected' => false,
            ],
            // Should never redirect to MFA if user has no CMS access
            // Note that this is altered by the "requires_admin_access" config, which is tested separately.
            'no cms access, optional for all' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
            'no cms access, required for all' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
            'no cms access, not in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
            'no cms access, not in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
            'no cms access, in optional group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['nocmsgroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
            'no cms access, in required group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'mfaGracePeriodExpires' => null,
                'requiredGroupFixtures' => ['nocmsgroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
        ];
        // Add scenarios with past and future expiry dates
        // See the setUp method, which sets the current datetime to "2019-01-25 12:00:00"
        // Note that the grace period doesn't affect the expected return value. These scenarios are here
        // to ensure that doesn't change unexpectedly.
        foreach ($scenarios as $name => $scenario) {
            // Past expiry dates
            $pastScenario = $scenario;
            $pastScenario['mfaGracePeriodExpires'] = '2018-12-25';
            $scenarios[$name . ' with past expiry'] = $pastScenario;
            // Future expiry dates
            $futureScenario = $scenario;
            $futureScenario['mfaGracePeriodExpires'] = '2019-01-30';
            $scenarios[$name . ' with future expiry'] = $futureScenario;
        }
        return $scenarios;
    }

    /**
     * @dataProvider provideShouldRedirectToMFA
     */
    public function testShouldRedirectToMFA(
        bool $mfaRequired,
        string $mfaAppliesTo,
        ?string $mfaGracePeriodExpires,
        array $requiredGroupFixtures,
        string $memberFixture,
        bool $expected
    ) {
        $config = SiteConfig::current_site_config();
        foreach ($requiredGroupFixtures as $fixtureName) {
            $group = $this->objFromFixture(Group::class, $fixtureName);
            $config->MFAGroupRestrictions()->add($group);
        }
        $siteConfigUpdate = ['MFARequired' => $mfaRequired, 'MFAAppliesTo' => $mfaAppliesTo];
        if ($mfaGracePeriodExpires) {
            $siteConfigUpdate['MFAGracePeriodExpires'] = $mfaGracePeriodExpires;
        }
        $this->setSiteConfig($siteConfigUpdate);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, $memberFixture);
        $this->logInAs($member);

        $this->assertSame($expected, EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function provideShouldRedirectToMFAWithoutCMSAccess()
    {
        return [
            'optional, applies to everyone' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'required, applies to everyone' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_EVERYONE,
                'requiredGroupFixtures' => [],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'optional, not in group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
            'required, not in group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'requiredGroupFixtures' => ['admingroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => false,
            ],
            'optional, in group' => [
                'MFARequired' => false,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'requiredGroupFixtures' => ['nocmsgroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
            'required, in group' => [
                'MFARequired' => true,
                'MFAAppliesTo' => EnforcementManager::APPLIES_TO_GROUPS,
                'requiredGroupFixtures' => ['nocmsgroup'],
                'memberFixture' => 'sammy_smith',
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider provideShouldRedirectToMFAWithoutCMSAccess
     */
    public function testShouldRedirectToMFAWithoutCMSAccess(
        bool $mfaRequired,
        string $mfaAppliesTo,
        array $requiredGroupFixtures,
        string $memberFixture,
        bool $expected
    ) {
        EnforcementManager::config()->set('requires_admin_access', false);
        $this->testShouldRedirectToMFA(
            $mfaRequired,
            $mfaAppliesTo,
            null,
            $requiredGroupFixtures,
            $memberFixture,
            $expected
        );
    }

    public function testShouldNotRedirectToMFAWhenConfigIsDisabled()
    {
        EnforcementManager::config()->set('enabled', false);
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $shouldRedirect = EnforcementManager::create()->shouldRedirectToMFA($member);
        $this->assertFalse($shouldRedirect);
    }

    public function testShouldNotRedirectToMFAWhenNoMethodsAreRegisteredInTheSystem()
    {
        $this->setSiteConfig(['MFARequired' => true]);
        MethodRegistry::config()->set('methods', []);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->logInAs($member);

        $this->assertFalse(EnforcementManager::create()->shouldRedirectToMFA($member));
    }

    public function testGracePeriodIsNotInEffectWhenMFAIsRequiredButNoGracePeriodIsSet()
    {
        $this->setSiteConfig(['MFARequired' => true]);
        $this->assertFalse(EnforcementManager::create()->isGracePeriodInEffect());
    }

    public function testUserHasCompletedRegistrationWhenBackupMethodIsDisabled()
    {
        MethodRegistry::config()->set('default_backup_method', null);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');

        $this->assertTrue(EnforcementManager::create()->hasCompletedRegistration($member));
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
