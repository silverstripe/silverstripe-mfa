<?php

namespace SilverStripe\MFA\Tests\Service;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\BackupCode\Method as BackupCodeMethod;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\Security\Member;

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
}
