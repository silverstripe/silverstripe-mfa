<?php

namespace SilverStripe\MFA\Tests\Extension\AccountReset;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Extension\AccountReset\MemberExtension;
use SilverStripe\MFA\Extension\AccountReset\SecurityAdminExtension;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\Security\Member;

/**
 * Class SecurityExtensionTest
 *
 * @package SilverStripe\MFA\Tests\Extension\AccountReset
 */
class SecurityExtensionTest extends FunctionalTest
{
    protected static $fixture_file = 'SecurityExtensionTest.yml';

    protected function setUp(): void
    {
        parent::setUp();

        $validator = Member::password_validator();
        // Do not let project code rules for password strength break these tests
        if ($validator) {
            $validator
                ->setMinLength(6)
                ->setMinTestScore(1);
        }
    }

    public function testResetAccountFailsWhenAlreadyAuthenticated()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');
        $this->logInAs($member);

        $token = $member->generateAccountResetTokenAndStoreHash();

        $url = (new SecurityAdminExtension())->getAccountResetLink($member, $token);
        $response = $this->get($url);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Already authenticated', $response->getBody());
    }

    public function testResetAccountFailsWithInvalidToken()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');
        $member->generateAccountResetTokenAndStoreHash();

        $url = (new SecurityAdminExtension())->getAccountResetLink($member, 'not-actually-the-token');
        $response = $this->get($url);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid member or token', $response->getBody());
    }

    public function testResetAccountFailsWithExpiredToken()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');

        // Wrap token generation in old timestamp to guarantee token expiry
        DBDatetime::set_mock_now('2011-11-26 17:00');
        $token = $member->generateAccountResetTokenAndStoreHash();
        DBDatetime::clear_mock_now();

        $url = (new SecurityAdminExtension())->getAccountResetLink($member, $token);
        $response = $this->get($url);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertStringContainsString('Invalid member or token', $response->getBody());
    }

    public function testResetAccountSubmissionFailsWithExpiredSession()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');
        $token = $member->generateAccountResetTokenAndStoreHash();

        $url = (new SecurityAdminExtension())->getAccountResetLink($member, $token);
        $response = $this->get($url);

        $this->assertEquals(200, $response->getStatusCode(), $response->getBody());

        // Simulate expired session (can't call destroy() due to issue in SilverStripe 4.1
        $this->session()->restart(new HTTPRequest('GET', '/'));

        $response = $this->submitForm(
            'Form_ResetAccountForm',
            null,
            ['NewPassword1' => 'testtest', 'NewPassword2' => 'testtest']
        );

        $this->assertStringContainsString('The account reset process timed out', $response->getBody());
    }

    public function testResetAccountSubmissionPasses()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');
        $token = $member->generateAccountResetTokenAndStoreHash();

        $url = (new SecurityAdminExtension())->getAccountResetLink($member, $token);
        $response = $this->get($url);

        $this->assertEquals(200, $response->getStatusCode(), $response->getBody());

        $response = $this->submitForm(
            'Form_ResetAccountForm',
            null,
            ['NewPassword1' => 'testtest', 'NewPassword2' => 'testtest']
        );

        // User should have been redirected to Login form with session message
        $this->assertStringContainsString('Login', $response->getBody());
        $this->assertStringContainsString('Reset complete', $response->getBody());
    }
}
