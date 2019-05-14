<?php

namespace SilverStripe\MFA\Tests\Authenticator;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Authenticator\MemberAuthenticator;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

class ChangePasswordHandlerTest extends FunctionalTest
{
    protected static $fixture_file = 'ChangePasswordHandlerTest.yml';

    protected function setUp()
    {
        parent::setUp();

        Config::modify()
            ->set(MethodRegistry::class, 'methods', [Method::class])
            ->set(Member::class, 'auto_login_token_lifetime', 10);

        SiteConfig::current_site_config()->update(['MFAEnabled' => true])->write();
    }

    /**
     * @param Member $member
     * @param string $password
     * @return HTTPResponse
     */
    protected function doLogin(Member $member, $password)
    {
        $this->get('Security/changepassword');

        return $this->submitForm(
            'MemberLoginForm_LoginForm',
            null,
            [
                'Email' => $member->Email,
                'Password' => $password,
                'AuthenticationMethod' => MemberAuthenticator::class,
                'action_doLogin' => 1,
            ]
        );
    }

    public function testMFADoesNotLoadWhenAUserIsLoggedIn()
    {
        $this->logInAs('simon');
        $response = $this->get('Security/changepassword');
        $this->assertContains('OldPassword', $response->getBody());
    }

    public function testMFADoesNotLoadWhenAUserDoesNotHaveRegisteredMethods()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');
        $memberId = $member->ID;
        $token = $member->generateAutologinTokenAndStoreHash();
        $response = $this->get("Security/changepassword?m={$memberId}&t={$token}");

        $this->assertContains('NewPassword1', $response->getBody(), 'There should be a new password field');
        $this->assertContains('NewPassword2', $response->getBody(), 'There should be a confirm new password field');
    }

    public function testMFALoadsWhenAUserHasConfiguredMethods()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'robbie');
        $memberId = $member->ID;
        $token = $member->generateAutologinTokenAndStoreHash();
        $response = $this->get("Security/changepassword?m={$memberId}&t={$token}");

        $this->assertNotContains('type="password"', $response->getBody(), 'Password form should be circumvented');
        $this->assertContains('id="mfa-app"', $response->getBody(), 'MFA screen should be displayed');
    }
}
