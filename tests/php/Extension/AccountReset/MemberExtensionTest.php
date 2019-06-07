<?php

namespace SilverStripe\MFA\Tests\Extension\AccountReset;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Extension\AccountReset\MemberExtension;
use SilverStripe\Security\Member;

class MemberExtensionTest extends SapphireTest
{
    protected static $fixture_file = 'MemberExtensionTest.yml';

    public function testAccountResetTokenIsGeneratedAndStored()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');

        $token = $member->generateAccountResetTokenAndStoreHash();

        // Refresh to ensure the changes have been stored
        $member = Member::get()->byID($member->ID);

        $this->assertNotEmpty($token);
        $this->assertNotEmpty($member->AccountResetHash);
        $this->assertNotEmpty($member->AccountResetExpired);
    }

    public function testGeneratedTokenCanBeVerified()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');

        $token = $member->generateAccountResetTokenAndStoreHash();

        $this->assertTrue($member->verifyAccountResetToken($token));
        $this->assertFalse($member->verifyAccountResetToken('BADTOKEN'));
    }
}
