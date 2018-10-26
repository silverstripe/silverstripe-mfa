<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Generators\CodeGenerator;
use Firesphere\BootstrapMFA\Handlers\BootstrapMFALoginHandler;
use Firesphere\BootstrapMFA\Models\BackupCode;
use Firesphere\BootstrapMFA\Tests\Helpers\CodeHelper;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;

class BootstrapMFAAuthenticatorTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/member.yml';

    /**
     * @var BootstrapMFAAuthenticator
     */
    protected $authenticator;

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

        $codes = CodeHelper::getCodesFromSession();
        $length = Config::inst()->get(CodeGenerator::class, 'length');

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

    public function testValidateBackupCodeWrong()
    {
        $member = $this->objFromFixture(Member::class, 'member1');

        $this->authenticator->validateBackupCode($member, '12345', $result);

        $this->assertInstanceOf(ValidationResult::class, $result);
        $this->assertFalse($result->isValid());
    }

    public function testGetLoginHandler()
    {
        $handler = $this->authenticator->getLoginHandler('/Security/login');

        $this->assertInstanceOf(BootstrapMFALoginHandler::class, $handler);
    }

    protected function setUp()
    {
        $this->authenticator = Injector::inst()->get(BootstrapMFAAuthenticator::class);
        Config::modify()->set(BackupCode::class, 'token_limit', 3);

        return parent::setUp();
    }
}
