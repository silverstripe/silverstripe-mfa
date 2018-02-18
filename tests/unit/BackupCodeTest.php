<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Generators\CodeGenerator;
use Firesphere\BootstrapMFA\Models\BackupCode;
use Firesphere\BootstrapMFA\Tests\Helpers\CodeHelper;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\IdentityStore;
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

    public function testTokenLimit()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = CodeHelper::getCodesFromSession();
        // Default length
        $this->assertEquals(15, count($codes));

        Config::modify()->set(BackupCode::class, 'token_limit', 10);

        BackupCode::generateTokensForMember($member);
        $codes = CodeHelper::getCodesFromSession();
        $this->assertEquals(10, count($codes));
    }

    public function testBackupCodeConfigNumeric()
    {
        Config::modify()->set(BackupCode::class, 'token_limit', 3);
        Config::modify()->set(CodeGenerator::class, 'length', 10);
        Config::modify()->set(CodeGenerator::class, 'type', 'numeric');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = CodeHelper::getCodesFromSession();

        // Actual testing
        foreach ($codes as $code) {
            $this->assertEquals(10, strlen($code));
            $this->assertTrue(is_numeric($code));
        }
    }

    public function testBackupCodeConfigAlpha()
    {
        Config::modify()->set(BackupCode::class, 'token_limit', 3);
        Config::modify()->set(CodeGenerator::class, 'type', 'characters');
        Config::modify()->set(CodeGenerator::class, 'case', 'mixed');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = CodeHelper::getCodesFromSession();

        // Actual testing
        foreach ($codes as $code) {
            $this->assertTrue(ctype_alpha($code));
            $this->assertFalse(is_numeric($code));
            $this->assertFalse(ctype_upper($code));
            $this->assertFalse(ctype_lower($code));
        }
    }

    public function testBackupCodeConfigAlphaUpper()
    {
        Config::modify()->set(BackupCode::class, 'token_limit', 3);
        Config::modify()->set(CodeGenerator::class, 'type', 'characters');
        Config::modify()->set(CodeGenerator::class, 'case', 'upper');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = CodeHelper::getCodesFromSession();

        // Actual testing
        foreach ($codes as $code) {
            $this->assertFalse(is_numeric($code));
            $this->assertTrue(ctype_alpha($code));
            $this->assertTrue(ctype_upper($code));
        }
    }

    public function testBackupCodeConfigAlphaLower()
    {
        Config::modify()->set(BackupCode::class, 'token_limit', 3);
        Config::modify()->set(CodeGenerator::class, 'type', 'characters');
        Config::modify()->set(CodeGenerator::class, 'case', 'lower');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = CodeHelper::getCodesFromSession();

        // Actual testing
        foreach ($codes as $code) {
            $this->assertFalse(is_numeric($code));
            $this->assertTrue(ctype_alpha($code));
            $this->assertTrue(ctype_lower($code));
        }
    }

    public function testBackupCodeConfigDefault()
    {
        Config::modify()->set(BackupCode::class, 'token_limit', 3);
        Config::modify()->set(CodeGenerator::class, 'type', 'bla');
        Config::modify()->set(CodeGenerator::class, 'case', 'bla');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = CodeHelper::getCodesFromSession();

        // Actual testing
        foreach ($codes as $code) {
            $this->assertTrue(is_numeric($code));
            $this->assertEquals('mixed', CodeGenerator::inst()->getCase());
            $this->assertEquals('numeric', CodeGenerator::inst()->getType());
        }
    }

    protected function setUp()
    {
        // Set to an idiotic length so we don't accidentally hit
        // a alphanumeric that due to fluke of randomness
        // only contains numbers, etc.
        Config::modify()->set(CodeGenerator::class, 'length', 100);

        return parent::setUp(); // TODO: Change the autogenerated stub
    }
}
