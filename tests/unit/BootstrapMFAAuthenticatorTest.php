<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Generators\CodeGenerator;
use Firesphere\BootstrapMFA\Models\BackupCode;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;

class BootstrapMFAAuthenticatorTest extends SapphireTest
{
    /**
     * @var BootstrapMFAAuthenticator
     */
    protected $authenticator;

    protected static $fixture_file = '../fixtures/member.yml';

    protected function setUp()
    {
        $this->authenticator = Injector::inst()->get(BootstrapMFAAuthenticator::class);

        return parent::setUp();
    }

    protected function getCodesFromSession()
    {
        // Funky stuff, extract the codes from the session message
        /** @var Session $session */
        $session = Controller::curr()->getRequest()->getSession();

        $message = $session->get('tokens');

        $message = str_replace('<p>Here are your tokens, please store them securily. ' .
            'They are stored encrypted and can not be recovered, only reset.</p><p>', '', $message);
        $codes = explode('<br />', $message);

        // Remove the <p> at the end
        array_pop($codes);

        return $codes;
    }

    /**
     * Test if user codes are properly validated and expired
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \SilverStripe\ORM\ValidationException
     * @throws \SilverStripe\Security\PasswordEncryptor_NotFoundException
     */
    public function testValidateBackupCodeRight()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = $this->getCodesFromSession();
        $length = Config::inst()->get(CodeGenerator::class,'length');

        // Actual testing
        foreach ($codes as $code) {
            $this->assertEquals($length, strlen($code));
            $member = $this->authenticator->validateBackupCode($member, $code, $result);
            // All codes should be valid
            $this->assertTrue($result->isValid());
            $this->assertInstanceOf(Member::class, $member);

            $encryptedCode = $member->encryptWithUserSettings($code);

            /** @var BackupCode $code */
            $code = BackupCode::get()->filter(['Code' => $encryptedCode])->first();

            $this->assertTrue((bool)$code->Used);
        }
    }

    public function testBackupCodeConfigNumeric()
    {
        Config::modify()->set(CodeGenerator::class, 'length', 10);
        Config::modify()->set(CodeGenerator::class, 'type', 'numeric');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = $this->getCodesFromSession();

        // Actual testing
        foreach ($codes as $code) {
            $this->assertEquals(10, strlen($code));
            $this->assertTrue(is_numeric($code));
            $member = $this->authenticator->validateBackupCode($member, $code, $result);
            // All codes should be valid
            $this->assertTrue($result->isValid());
            $this->assertInstanceOf(Member::class, $member);

            $encryptedCode = $member->encryptWithUserSettings($code);

            /** @var BackupCode $code */
            $code = BackupCode::get()->filter(['Code' => $encryptedCode])->first();
            $this->assertTrue((bool)$code->Used);
        }
    }

    public function testBackupCodeConfigAlpha()
    {
        Config::modify()->set(CodeGenerator::class, 'type', 'characters');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = $this->getCodesFromSession();

        // Actual testing
        foreach ($codes as $code) {
            $this->assertTrue(ctype_alpha($code));
            $this->assertFalse(is_numeric($code));
            $this->assertFalse(ctype_upper($code));
            $this->assertFalse(ctype_lower($code));
            $member = $this->authenticator->validateBackupCode($member, $code, $result);
            // All codes should be valid
            $this->assertTrue($result->isValid());
            $this->assertInstanceOf(Member::class, $member);

            $encryptedCode = $member->encryptWithUserSettings($code);

            /** @var BackupCode $code */
            $code = BackupCode::get()->filter(['Code' => $encryptedCode])->first();
            $this->assertTrue((bool)$code->Used);
        }
    }

    public function testBackupCodeConfigAlphaUpper()
    {
        Config::modify()->set(CodeGenerator::class, 'type', 'characters');
        Config::modify()->set(CodeGenerator::class, 'case', 'upper');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = $this->getCodesFromSession();

        // Actual testing
        foreach ($codes as $code) {
            $this->assertFalse(is_numeric($code));
            $this->assertTrue(ctype_alpha($code));
            $this->assertTrue(ctype_upper($code));
            $member = $this->authenticator->validateBackupCode($member, $code, $result);
            // All codes should be valid
            $this->assertTrue($result->isValid());
            $this->assertInstanceOf(Member::class, $member);

            $encryptedCode = $member->encryptWithUserSettings($code);

            /** @var BackupCode $code */
            $code = BackupCode::get()->filter(['Code' => $encryptedCode])->first();
            $this->assertTrue((bool)$code->Used);
        }
    }

    public function testBackupCodeConfigAlphaLower()
    {
        Config::modify()->set(CodeGenerator::class, 'type', 'characters');
        Config::modify()->set(CodeGenerator::class, 'case', 'lower');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($member);
        BackupCode::generateTokensForMember($member);

        $codes = $this->getCodesFromSession();

        // Actual testing
        foreach ($codes as $code) {
            $this->assertFalse(is_numeric($code));
            $this->assertTrue(ctype_alpha($code));
            $this->assertTrue(ctype_lower($code));
            $member = $this->authenticator->validateBackupCode($member, $code, $result);
            // All codes should be valid
            $this->assertTrue($result->isValid());
            $this->assertInstanceOf(Member::class, $member);

            $encryptedCode = $member->encryptWithUserSettings($code);

            /** @var BackupCode $code */
            $code = BackupCode::get()->filter(['Code' => $encryptedCode])->first();
            $this->assertTrue((bool)$code->Used);
        }
    }

    public function testValidateBackupCodeWrong()
    {
        $member = $this->objFromFixture(Member::class, 'member1');

        $this->authenticator->validateBackupCode($member, '12345', $result);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertFalse($result->isValid());
    }
}
