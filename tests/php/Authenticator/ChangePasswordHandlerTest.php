<?php

namespace SilverStripe\MFA\Tests\Authenticator;

use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Authenticator\MemberAuthenticator;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
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

        Injector::inst()->load([
            Security::class => [
                'properties' => [
                    'authenticators' => [
                        'default' => '%$' . MemberAuthenticator::class,
                    ]
                ]
            ],
            LoggerInterface::class . '.mfa' => [
                'class' => 'Monolog\Handler\NullHandler'
            ],
        ]);
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
            array(
                'Email' => $member->Email,
                'Password' => $password,
                'AuthenticationMethod' => MemberAuthenticator::class,
                'action_doLogin' => 1,
            )
        );
    }

    public function testMFADoesNotLoadWhenAUserIsLoggedIn()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'simon');
        $this->logInAs($member);
        $this->get('Security/changepassword');
        $this->assertNotEmpty($this->cssParser()->getByXpath('//input[@type="password"][@name="OldPassword"]'));
    }

    public function testMFADoesNotLoadWhenAUserDoesNotHaveRegisteredMethods()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');
        $m = $member->ID;
        $t = $member->generateAutologinTokenAndStoreHash();
        $this->get("Security/changepassword?m={$m}&t={$t}");
        $parser = $this->cssParser();
        $this->assertNotEmpty(
            $parser->getByXpath('//input[@type="password"][@name="NewPassword1"]'),
            'There should be a new password field'
        );
        $this->assertNotEmpty(
            $parser->getByXpath('//input[@type="password"][@name="NewPassword2"]'),
            'There should be a confirm new password field'
        );
    }

    public function testMFALoadsWhenAUserHasConfiguredMethods()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'robbie');
        $m = $member->ID;
        $t = $member->generateAutologinTokenAndStoreHash();
        $this->get("Security/changepassword?m={$m}&t={$t}");
        $parser = $this->cssParser();
        $this->assertEmpty($parser->getByXpath('//input[@type="password"]'));
        $mfaApp = $parser->getBySelector('#mfa-app');
        $this->assertNotEmpty($mfaApp);
        $this->assertCount(1, $mfaApp);
        $this->assertArraySubset(
            ['data-schemaurl' => "/Security/changepassword/mfa/schema"],
            current($mfaApp[0]->attributes())
        );
    }
}
