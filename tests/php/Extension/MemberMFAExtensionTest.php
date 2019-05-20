<?php

namespace SilverStripe\MFA\Tests\Extension;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

class MemberMFAExtensionTest extends SapphireTest
{
    protected static $fixture_file = 'MemberMFAExtensionTest.yml';

    public function testAdminUserCanViewButNotEditOthersMFAConfig()
    {
        $targetMember = $this->objFromFixture(Member::class, 'squib');

        $this->logInAs('admin');

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertFalse($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testAdminUserCanViewAndEditTheirOwnMFAConfig()
    {
        $targetMember = $this->objFromFixture(Member::class, 'admin');

        $this->logInAs($targetMember);

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertTrue($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testStandardUserCannotViewOrEditOthersMFAConfig()
    {
        $targetMember = $this->objFromFixture(Member::class, 'admin');

        $this->logInAs('squib');

        $this->assertFalse($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertFalse($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testStandardUserCanViewAndEditTheirOwnMFAConfig()
    {
        $targetMember = $this->objFromFixture(Member::class, 'squib');

        $this->logInAs($targetMember);

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertTrue($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }
}
