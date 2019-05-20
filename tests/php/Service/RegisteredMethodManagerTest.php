<?php

namespace SilverStripe\MFA\Tests\Service;

use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\BackupCode\Method as BackupCodeMethod;
use SilverStripe\MFA\Extension\MemberMFAExtension;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class RegisteredMethodManagerTest extends SapphireTest
{
    protected static $fixture_file = 'RegisteredMethodManagerTest.yml';

    protected static $required_extensions = [
        Member::class => [
            MemberMFAExtension::class,
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
        /** @var Member&MemberMFAExtension $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $method = new BackupCodeMethod();

        $this->assertCount(1, $member->RegisteredMFAMethods());
        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['foo' => 'bar']);

        $this->assertCount(1, $member->RegisteredMFAMethods());
        $this->assertSame(['foo' => 'bar'], json_decode($member->RegisteredMFAMethods()->first()->Data, true));
    }

    public function testRegisterForMemberCreatesNewMethod()
    {
        /** @var Member&MemberMFAExtension $member */
        $member = Member::create(['FirstName' => 'Mike']);
        $member->write();
        $method = new BackupCodeMethod();

        $this->assertCount(0, $member->RegisteredMFAMethods());
        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['foo', 'bar']);
        $this->assertCount(1, $member->RegisteredMFAMethods());
    }

    public function testRegisterForMemberSendsNotification()
    {
        /** @var Member&MemberExtension $member */
        $member = Member::create(['FirstName' => 'Mike', 'Email' => 'test@example.com']);
        $member->write();
        $method = new BasicMathMethod();

        $manager = RegisteredMethodManager::singleton();
        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['foo', 'bar']);

        $this->assertEmailSent($member->Email, null, '/method was added to your account/');
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
        /** @var Member&MemberMFAExtension $member */
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

        $this->assertEmailSent($member->Email, null, '/method was removed from your account/');
    }

    public function testDeletingLastMethodRemovesBackupCodes()
    {
        // Assert the config is set for backup codes
        Config::modify()->set(MethodRegistry::class, 'default_backup_method', BackupCodeMethod::class);

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

        $this->assertNull(DataObject::get_by_id(RegisteredMethod::class, $backupMethod->ID));
        $this->assertNull(DataObject::get_by_id(RegisteredMethod::class, $mathMethod->ID));
    }
}
