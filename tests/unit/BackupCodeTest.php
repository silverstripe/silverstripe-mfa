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

    public function testCodesGenerated()
    {

        $member = $this->objFromFixture(Member::class, 'member1');
        Security::setCurrentUser($member);

        BackupCode::get()->removeAll();

        BackupCode::generateTokensForMember($member);

        $codes = BackupCode::get()->filter(['MemberID' => $member->ID]);

        $this->assertGreaterThan(0, $codes->count());

        $codesFromValid = BackupCode::getValidTokensForMember($member);

        $this->assertEquals($codes->count(), $codesFromValid->count());

    }

    public function testCanEdit()
    {
        $backup = Injector::inst()->get(BackupCode::class);

        $this->assertFalse($backup->canEdit());
    }

    public function testExpiry()
    {
        $member = $this->objFromFixture(Member::class, 'member1');
        Security::setCurrentUser($member);

        BackupCode::generateTokensForMember($member);
        /** @var BackupCode $code */
        $code = BackupCode::get()->filter(['MemberID' => $member->ID])->first();

        $code = $code->expire();

        $this->assertTrue((bool)$code->Used);

        $code = BackupCode::get()->byID($code->ID);

        $this->assertTrue((bool)$code->Used);
    }

}