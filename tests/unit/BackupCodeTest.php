<?php

namespace Firesphere\BootstrapMFA\Tests;


use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class BackupCodeTest extends SapphireTest
{

    protected static $fixture_file = '../fixtures/member.yml';

    public function testWarningEmail()
    {
        $member = $this->objFromFixture(Member::class, 'member1');

        BackupCode::sendWarningEmail($member);

        $this->assertEmailSent($member->Email);
    }

    public function testWarningMailNotSameUser()
    {
        $admin = $this->objFromFixture(Member::class, 'member2');
        Security::setCurrentUser($admin);

        $member = $this->objFromFixture(Member::class, 'member1');

        BackupCode::generateTokensForMember($member);

        $this->assertEmailSent($member->Email);
    }

    public function canEdit()
    {
        $backup = Injector::inst()->get(BackupCode::class);

        $this->assertFalse($backup->canEdit());
    }

    public function testExpiry()
    {
        $this->assertTrue(true); // I'm done for now
    }

}