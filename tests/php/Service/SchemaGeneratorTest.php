<?php

namespace SilverStripe\MFA\Tests\Service;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

class SchemaGeneratorTest extends SapphireTest
{
    protected static $fixture_file = 'SchemaGeneratorTest.yml';

    /**
     * @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var StoreInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $store;

    /**
     * @var SchemaGenerator
     */
    protected $generator;

    protected function setUp()
    {
        parent::setUp();

        DBDatetime::set_mock_now('2019-01-25 12:00:00');

        $this->setSiteConfig(['MFAEnabled' => true]);

        $this->request = $this->createMock(HTTPRequest::class);
        $this->store = $this->createMock(StoreInterface::class);

        MethodRegistry::config()->set('methods', [
            BasicMathMethod::class,
        ]);

        $this->generator = new SchemaGenerator($this->request, $this->store);
    }

    /**
     * @expectedException \SilverStripe\MFA\Exception\MemberNotFoundException
     */
    public function testGetMemberThrowsExceptionWithoutMember()
    {
        $this->logOut();
        $this->generator->getMember();
    }

    public function testGetSchema()
    {
        $this->logInAs('sally_smith');

        $schema = $this->generator->getSchema();

        $this->assertArrayHasKey('registeredMethods', $schema);
        $this->assertNotEmpty($schema['registeredMethods']);
        $this->assertArrayHasKey('availableMethods', $schema);
        $this->assertNotEmpty($schema['availableMethods']);
        $this->assertArrayHasKey('defaultMethod', $schema);
        $this->assertNotEmpty($schema['defaultMethod']);

        $this->assertSame('backup-codes', $schema['registeredMethods'][0]['urlSegment']);
        $this->assertSame('basic-math', $schema['availableMethods'][0]['urlSegment']);
        $this->assertSame('backup-codes', $schema['defaultMethod']);
    }

    public function testCannotSkipWhenMFAIsRequiredWithNoGracePeriod()
    {
        $this->setSiteConfig(['MFARequired' => true]);

        $schema = $this->generator->getSchema();
        $this->assertFalse($schema['canSkip']);
    }

    public function testCanSkipWhenMFAIsRequiredWithGracePeriodExpiringInFuture()
    {
        $this->setSiteConfig(['MFARequired' => true, 'MFAGracePeriodExpires' => '2019-01-30']);

        $schema = $this->generator->getSchema();
        $this->assertTrue($schema['canSkip']);
    }

    public function testCannotSkipWhenMFAIsRequiredWithGracePeriodExpiringInPast()
    {
        $this->setSiteConfig(['MFARequired' => true, 'MFAGracePeriodExpires' => '2018-12-25']);

        $schema = $this->generator->getSchema();
        $this->assertFalse($schema['canSkip']);
    }

    public function testCannotSkipWhenMemberHasRegisteredAuthenticationMethodsSetUp()
    {
        $this->setSiteConfig(['MFARequired' => false]);
        // Sally has "backup codes" as a registered authentication method already
        $this->logInAs('sally_smith');

        $schema = $this->generator->getSchema();
        $this->assertFalse($schema['canSkip']);
    }

    public function testCanSkipWhenMFAIsOptional()
    {
        $this->setSiteConfig(['MFARequired' => false]);
        // Anonymous admin user
        $this->logInWithPermission();

        $schema = $this->generator->getSchema();
        $this->assertTrue($schema['canSkip']);
    }

    public function testShouldRedirectToMFAWhenMFAIsRequired()
    {
        $this->setSiteConfig(['MFARequired' => true]);
        $this->logInAs('sally_smith');

        $schema = $this->generator->getSchema();
        $this->assertTrue($schema['shouldRedirect']);
    }

    public function testShouldRedirectToMFAWhenMFAIsOptionalAndHasNotBeenSkipped()
    {
        $this->setSiteConfig(['MFARequired' => false]);

        /** @var Member|MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->HasSkippedMFARegistration = false;
        $member->write();
        $this->logInAs($member);

        $schema = $this->generator->getSchema();
        $this->assertTrue($schema['shouldRedirect']);
    }

    public function testShouldNotRedirectToMFAWhenMFAIsOptionalAndHasBeenSkipped()
    {
        $this->setSiteConfig(['MFARequired' => false]);

        /** @var Member|MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->HasSkippedMFARegistration = true;
        $member->write();
        $this->logInAs($member);

        $schema = $this->generator->getSchema();
        $this->assertFalse($schema['shouldRedirect']);
    }

    /**
     * Helper method for changing the current SiteConfig values
     *
     * @param array $data
     */
    protected function setSiteConfig($data)
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->update($data);
        $siteConfig->write();
    }
}
