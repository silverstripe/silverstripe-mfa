<?php

namespace SilverStripe\MFA\Tests\Extension;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Class MemberExtensionTest
 *
 * @group wip
 * @package SilverStripe\MFA\Tests\Extension
 */
class MemberExtensionTest extends SapphireTest
{
    protected static $fixture_file = 'MemberExtensionTest.yml';

    public function testAdminUserCanViewButNotEditOthersMFAConfig()
    {
        $adminMember = $this->objFromFixture(Member::class, 'admin');
        $targetMember = $this->objFromFixture(Member::class, 'squib');

        Security::setCurrentUser($adminMember);

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertFalse($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testAdminUserCanViewAndEditTheirOwnMFAConfig()
    {
        $adminMember = $this->objFromFixture(Member::class, 'admin');
        $targetMember = $adminMember;

        Security::setCurrentUser($adminMember);

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertTrue($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testStandardUserCannotViewOrEditOthersMFAConfig()
    {
        $regularMember = $this->objFromFixture(Member::class, 'squib');
        $targetMember = $this->objFromFixture(Member::class, 'admin');

        Security::setCurrentUser($regularMember);

        $this->assertFalse($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertFalse($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testStandardUserCanViewAndEditTheirOwnMFAConfig()
    {
        $regularMember = $this->objFromFixture(Member::class, 'squib');
        $targetMember = $regularMember;

        Security::setCurrentUser($regularMember);

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertTrue($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }
}
