<?php

namespace SilverStripe\MFA\Tests\Extension\AccountReset;

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

    public function testResetAccountFailsWhenAlreadyAuthenticated()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');
        $this->logInAs($member);

        $token = $member->generateAccountResetTokenAndStoreHash();

        $url = (new SecurityAdminExtension())->getAccountResetLink($member, $token);
        $response = $this->get($url);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Already authenticated', $response->getBody());
    }

    public function testResetAccountFailsWithInvalidToken()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');
        $member->generateAccountResetTokenAndStoreHash();

        $url = (new SecurityAdminExtension())->getAccountResetLink($member, 'not-actually-the-token');
        $response = $this->get($url);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertContains('Invalid member or token', $response->getBody());
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
        $this->assertContains('Invalid member or token', $response->getBody());
    }

    public function testResetAccountSubmissionFailsWithExpiredSession()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'user');
        $token = $member->generateAccountResetTokenAndStoreHash();

        $url = (new SecurityAdminExtension())->getAccountResetLink($member, $token);
        $response = $this->get($url);

        $this->assertEquals(200, $response->getStatusCode(), $response->getBody());

        // Simulate expired session
        $this->session()->destroy(true);

        $response = $this->submitForm(
            'Form_ResetAccountForm',
            null,
            ['NewPassword1' => 'testtest', 'NewPassword2' => 'testtest']
        );

        $this->assertContains('The account reset process timed out', $response->getBody());
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
        $this->assertContains('Login', $response->getBody());
        $this->assertContains('Reset complete', $response->getBody());
    }
}
