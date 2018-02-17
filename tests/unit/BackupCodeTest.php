<?php

namespace Firesphere\BootstrapMFA\Tests;


use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

class BackupCodeTest extends SapphireTest
{

    protected static $fixture_file = '../fixtures/member.yml';

    public function testWarningEmail()
    {
        $member = $this->objFromFixture(Member::class, 'member1');

        BackupCode::sendWarningEmail($member);

        $this->assertEmailSent($member->Email);
    }

}