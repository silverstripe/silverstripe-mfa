<?php

namespace SilverStripe\MFA\Tests\Extension;

use SapphireTest;
use SilverStripe\MFA\BackupCode\Method;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use Member;

class MemberExtensionTest extends SapphireTest
{
    protected static $fixture_file = 'MemberExtensionTest.yml';

    public function testAdminUserCanViewButNotEditOthersMFAConfig()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'squib');
        /** @var Member&MemberExtension $adminMember */
        $adminMember = $this->objFromFixture(Member::class, 'admin');

        $adminMember->logIn();

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertFalse($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testAdminUserCanViewAndEditTheirOwnMFAConfig()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'admin');

        $targetMember->logIn();

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertTrue($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testStandardUserCannotViewOrEditOthersMFAConfig()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'admin');
        /** @var Member&MemberExtension $standardMember */
        $standardMember = $this->objFromFixture(Member::class, 'squib');

        $standardMember->logIn();

        $this->assertFalse($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertFalse($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testStandardUserCanViewAndEditTheirOwnMFAConfig()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'squib');

        $targetMember->logIn();

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertTrue($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    /**
     * @expectedException \SilverStripe\MFA\Exception\InvalidMethodException
     * @expectedExceptionMessage The provided method does not belong to this member
     */
    public function testSetDefaultRegisteredMethodThrowsExceptionWhenSettingSomeoneElsesMethodAsDefault()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'admin');
        $method = new Method();
        RegisteredMethodManager::singleton()->registerForMember($targetMember, $method, ['foo' => 'bar']);
        $registeredMethod = RegisteredMethodManager::singleton()->getFromMember($targetMember, $method);

        /** @var Member&MemberExtension $anotherMember */
        $anotherMember = $this->objFromFixture(Member::class, 'squib');
        $anotherMember->setDefaultRegisteredMethod($registeredMethod);
    }
}
