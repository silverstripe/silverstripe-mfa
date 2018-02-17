<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\Member;

class MemberExtensionTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/member.yml';

    public function testMemberCodesExpired()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');

        $member->updateMFA = true;
        $member->write();

        /** @var DataList|BackupCode $codes */
        $codes = $member->BackupCodes();

        $member->updateMFA = true;
        $member->write();

        foreach ($codes as $code) {
            /** @var BackupCode $backup */
            $backup = BackupCode::get()->byID($code->ID);
            $this->assertNull($backup);
        }
    }

    public function testMemberCodesNotExpired()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');

        $member->updateMFA = true;
        $member->write();

        /** @var DataList|BackupCode $codes */
        $codes = $member->BackupCodes();

        $member->write();

        foreach ($codes as $code) {
            /** @var BackupCode $backup */
            $backup = BackupCode::get()->byID($code->ID);
            $this->assertNotNull($backup);
        }

    }
}
