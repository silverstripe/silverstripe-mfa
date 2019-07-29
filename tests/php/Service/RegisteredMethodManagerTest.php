<?php

namespace SilverStripe\MFA\Tests\Service;

use Config;
use Injector;
use SapphireTest;
use SilverStripe\MFA\BackupCode\Method as BackupCodeMethod;
use SilverStripe\MFA\Extension\MemberExtension;
use MFARegisteredMethod as RegisteredMethod;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use DataObject;
use Member;

class RegisteredMethodManagerTest extends SapphireTest
{
    protected static $fixture_file = 'RegisteredMethodManagerTest.yml';

    protected static $required_extensions = [
        Member::class => [
            MemberExtension::class,
        ],
    ];

    public function testGetFromMember()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');

        $result = RegisteredMethodManager::singleton()->getFromMember($member, new BackupCodeMethod());
        $this->assertInstanceOf(RegisteredMethod::class, $result);
    }

    public function testGetFromMemberReturnsNullWhenNotFound()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');

        $result = RegisteredMethodManager::singleton()->getFromMember($member, new BasicMathMethod());
        $this->assertNull($result);
    }

    public function testRegisterForMemberWritesToExistingRegisteredMethod()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $method = new BackupCodeMethod();

        $this->assertCount(1, $member->RegisteredMFAMethods());
        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['foo' => 'bar']);

        $this->assertCount(1, $member->RegisteredMFAMethods());
        $this->assertSame(['foo' => 'bar'], json_decode($member->RegisteredMFAMethods()->first()->Data, true));
    }

    public function testRegisterForMemberCreatesNewMethod()
    {
        /** @var Member&MemberExtension $member */
        $member = Member::create(['FirstName' => 'Mike']);
        $member->write();
        $method = new BackupCodeMethod();

        $this->assertCount(0, $member->RegisteredMFAMethods());
        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['foo', 'bar']);
        $this->assertCount(1, $member->RegisteredMFAMethods());
    }

    public function testRegisterForMemberAssignsDefaultRegisteredMethod()
    {
        /** @var Member&MemberExtension $member */
        $member = Member::create(['FirstName' => 'Mike']);
        $member->write();
        $method = new BackupCodeMethod();

        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['foo', 'bar']);
        $this->assertCount(1, $member->RegisteredMFAMethods());
        $defaultMethod = $member->getDefaultRegisteredMethod();
        $this->assertNotNull($defaultMethod, 'Default registered method should have been assigned');

        $newMethod = new BasicMathMethod();
        RegisteredMethodManager::singleton()->registerForMember($member, $newMethod, ['foo', 'baz']);
        $this->assertCount(2, $member->RegisteredMFAMethods());
        $this->assertSame(
            $defaultMethod->ID,
            $member->getDefaultRegisteredMethod()->ID,
            'Default registered method should not have changed'
        );
    }

    public function testRegisterForMemberSendsNotification()
    {
        /** @var Member&MemberExtension $member */
        $member = Member::create(['FirstName' => 'Mike', 'Email' => 'test@example.com']);
        $member->write();
        $method = new BasicMathMethod();

        $manager = RegisteredMethodManager::singleton();
        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['foo', 'bar']);

        $this->assertEmailSent(
            $member->Email,
            null,
            '/method was added to your account/',
            '/You have successfully registered/'
        );
    }

    public function testRegisterBackupMethodDoesNotSendEmail()
    {
        /** @var Member&MemberExtension $member */
        $member = Member::create(['FirstName' => 'Mike', 'Email' => 'test@example.com']);
        $member->write();
        $method = new BackupCodeMethod();

        $manager = RegisteredMethodManager::singleton();
        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['foo', 'bar']);

        $this->assertNull($this->findEmail($member->Email));
    }


    public function testRegisterForMemberDoesNothingWithNoData()
    {
        /** @var Member&MemberExtension $member */
        $member = Member::create(['FirstName' => 'Michelle']);
        $member->write();
        $method = new BackupCodeMethod();

        $this->assertCount(0, $member->RegisteredMFAMethods());
        RegisteredMethodManager::singleton()->registerForMember($member, $method, []);
        $this->assertCount(0, $member->RegisteredMFAMethods());
    }

    public function testDeleteFromMember()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'bob_jones');

        // Bob has 3 methods
        $this->assertCount(3, $member->RegisteredMFAMethods());

        $manager = RegisteredMethodManager::singleton();
        $manager->deleteFromMember($member, new BasicMathMethod());

        $this->assertCount(2, $member->RegisteredMFAMethods());
        $this->assertNull($manager->getFromMember($member, new BasicMathMethod()));
    }

    public function testDeleteFromMemberSendsNotification()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'bob_jones');

        $manager = RegisteredMethodManager::singleton();
        $manager->deleteFromMember($member, new BasicMathMethod());

        $this->assertEmailSent($member->Email, null, '/method was removed from your account/', '/You have removed/');
    }

    public function testDeletingLastMethodRemovesBackupCodes()
    {
        // Assert the config is set for backup codes
        Config::inst()->remove(MethodRegistry::class, 'default_backup_method');
        Config::inst()->update(MethodRegistry::class, 'default_backup_method', BackupCodeMethod::class);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'jane_doe');

        $manager = RegisteredMethodManager::singleton();
        $this->assertCount(2, $member->RegisteredMFAMethods());
        // Get methods from the member to assert orphan records are removed
        $backupMethod = $manager->getFromMember($member, new BackupCodeMethod());
        $mathMethod = $manager->getFromMember($member, new BasicMathMethod());

        $this->assertNotNull($backupMethod);
        $this->assertNotNull($mathMethod);

        $manager->deleteFromMember($member, new BasicMathMethod());

        $this->assertCount(0, $member->RegisteredMFAMethods());

        $this->assertFalse(DataObject::get_by_id(RegisteredMethod::class, $backupMethod->ID));
        $this->assertFalse(DataObject::get_by_id(RegisteredMethod::class, $mathMethod->ID));
    }

    public function testCanRemoveTheOnlyMethodWhenMFAIsOptional()
    {
        Config::inst()->remove(MethodRegistry::class, 'default_backup_method');
        Config::inst()->update(MethodRegistry::class, 'default_backup_method', BackupCodeMethod::class);
        $this->registerServiceForMFAToBeRequired(false);
        $jane = $this->objFromFixture(Member::class, 'jane_doe');
        $method = $this->objFromFixture(RegisteredMethod::class, 'math');
        $this->assertTrue(RegisteredMethodManager::create()->canRemoveMethod($jane, $method->getMethod()));
    }

    public function testCannotRemoveTheOnlyMethodWhenMFAIsRequired()
    {
        Config::inst()->remove(MethodRegistry::class, 'default_backup_method');
        Config::inst()->update(MethodRegistry::class, 'default_backup_method', BackupCodeMethod::class);
        $this->registerServiceForMFAToBeRequired(true);
        $jane = $this->objFromFixture(Member::class, 'jane_doe');
        $method = $this->objFromFixture(RegisteredMethod::class, 'math');
        $this->assertFalse(RegisteredMethodManager::create()->canRemoveMethod($jane, $method->getMethod()));
    }

    public function testCanRemoveOneOfTwoMethodsWhenMFAIsRequired()
    {
        Config::inst()->remove(MethodRegistry::class, 'default_backup_method');
        Config::inst()->update(MethodRegistry::class, 'default_backup_method', BackupCodeMethod::class);
        $this->registerServiceForMFAToBeRequired(true);
        $bob = $this->objFromFixture(Member::class, 'bob_jones');
        $method = $this->objFromFixture(RegisteredMethod::class, 'math2');
        $this->assertTrue(RegisteredMethodManager::create()->canRemoveMethod($bob, $method->getMethod()));
    }

    private function registerServiceForMFAToBeRequired($required = false)
    {
        $enforcementMock = $this->getMockBuilder(EnforcementManager::class)
            ->setMethods(['isMFARequired'])
            ->getMock();
        $enforcementMock
            ->method('isMFARequired')
            ->willReturn($required);
        Injector::inst()->registerService($enforcementMock, EnforcementManager::class);
    }
}
