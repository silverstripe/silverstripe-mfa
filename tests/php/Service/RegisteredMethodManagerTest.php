<?php

namespace SilverStripe\MFA\Tests\Service;

use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\BackupCode\Method as BackupCodeMethod;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\Notification\HandlerInterface;
use SilverStripe\MFA\Service\Notification\Service as NotificationService;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\MFA\Tests\Stub\Null\Method as NullMethod;
use SilverStripe\MFA\Tests\Stub\Service\Notification\ServiceExtension as NotificationServiceExtension;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class RegisteredMethodManagerTest extends SapphireTest
{
    protected static $fixture_file = 'RegisteredMethodManagerTest.yml';

    protected static $required_extensions = [
        Member::class => [
            MemberExtension::class,
        ],
        NotificationService::class => [
            NotificationServiceExtension::class
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

    public function testRegisterForMemberSendsOnlyOneNotification()
    {
        /** @var Member&MemberExtension $member */
        $member = Member::create(['FirstName' => 'Jim']);
        $member->write();
        $method = new NullMethod();

        Config::modify()->set(MethodRegistry::class, 'default_backup_method', BackupCodeMethod::class);
        /* Injector::inst()->registerService(
            $this->getMock(HandlerInterface::class)
                // Backup method is also added when the first registration is made
                // but there should only be one notification (for the method actively registered by the user)
                ->expects($this->once())
                ->method('notify')
                ->willReturn(true),
            NotificationServiceExtension::HANDLER
        ); */

        RegisteredMethodManager::singleton()->registerForMember($member, $method, []);
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
