<?php

namespace SilverStripe\MFA\Tests\Authenticator;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Authenticator\LoginHandler;
use SilverStripe\MFA\Authenticator\MemberAuthenticator;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\SiteConfig\SiteConfig;

class LoginHandlerTest extends FunctionalTest
{
    protected static $fixture_file = 'LoginHandlerTest.yml';

    protected function setUp()
    {
        parent::setUp();
        Config::modify()->set(MethodRegistry::class, 'methods', [Method::class]);

        Injector::inst()->load([
            Security::class => [
                'properties' => [
                    'authenticators' => [
                        'default' => '%$' . MemberAuthenticator::class,
                    ]
                ]
            ]
        ]);
    }

    public function testMFAStepIsAdded()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');

        $this->autoFollowRedirection = false;
        $response = $this->doLogin($member, 'Password123');
        $this->autoFollowRedirection = true;

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/Security/login/default/mfa', $response->getHeader('location'));
    }

    public function testMethodsNotBeingAvailableWillLogin()
    {
        Config::modify()->set(MethodRegistry::class, 'methods', []);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');

        // Ensure a URL is set to redirect to after successful login
        $this->session()->set('BackURL', 'something');

        $this->autoFollowRedirection = false;
        $response = $this->doLogin($member, 'Password123');
        $this->autoFollowRedirection = true;

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/something', $response->getHeader('location'));
    }

    public function testMFASchemaEndpointIsNotAccessibleByDefault()
    {
        // Assert that this endpoint is not available if you haven't started the login process
        $this->autoFollowRedirection = false;
        $response = $this->get('Security/login/default/mfa/schema');
        $this->autoFollowRedirection = true;

        $this->assertSame(302, $response->getStatusCode());
    }

    public function testMFASchemaEndpointReturnsMethodDetails()
    {
        // "Guy" isn't very security conscious - he has no MFA methods set up
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');
        $this->scaffoldPartialLogin($member);

        $result = $this->get('Security/login/default/mfa/schema');

        $response = json_decode($result->getBody(), true);

        $this->assertArrayHasKey('registeredMethods', $response);
        $this->assertArrayHasKey('availableMethods', $response);
        $this->assertArrayHasKey('defaultMethod', $response);

        $this->assertCount(0, $response['registeredMethods']);
        $this->assertCount(1, $response['availableMethods']);
        $this->assertNull($response['defaultMethod']);

        /** @var MethodInterface $method */
        $method = Injector::inst()->get(Method::class);
        $registerHandler = $method->getRegisterHandler();

        $this->assertSame([[
            'urlSegment' => $method->getURLSegment(),
            'name' => $registerHandler->getName(),
            'description' => $registerHandler->getDescription(),
            'supportLink' => $registerHandler->getSupportLink(),
        ]], $response['availableMethods']);
    }

    public function testMFASchemaEndpointShowsRegisteredMethodsIfSetUp()
    {
        // "Simon" is security conscious - he uses the cutting edge MFA methods
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'simon');
        $this->scaffoldPartialLogin($member);

        $result = $this->get('Security/login/default/mfa/schema');

        $response = json_decode($result->getBody(), true);

        $this->assertArrayHasKey('registeredMethods', $response);
        $this->assertArrayHasKey('availableMethods', $response);
        $this->assertArrayHasKey('defaultMethod', $response);

        $this->assertCount(1, $response['registeredMethods']);
        $this->assertCount(0, $response['availableMethods']);
        $this->assertNull($response['defaultMethod']);

        /** @var MethodInterface $method */
        $method = Injector::inst()->get(Method::class);
        $loginHandler = $method->getLoginHandler();

        $this->assertSame([[
            'urlSegment' => $method->getURLSegment(),
            'leadInLabel' => $loginHandler->getLeadInLabel()
        ]], $response['registeredMethods']);
    }

    public function testMFASchemaEndpointProvidesDefaultMethodIfSet()
    {
        // "Robbie" is security conscious and is also a CMS expert! He set up MFA and set a default method :o
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'robbie');
        $this->scaffoldPartialLogin($member);

        $result = $this->get('Security/login/default/mfa/schema');

        $response = json_decode($result->getBody(), true);

        $this->assertArrayHasKey('registeredMethods', $response);
        $this->assertArrayHasKey('availableMethods', $response);
        $this->assertArrayHasKey('defaultMethod', $response);

        $this->assertCount(1, $response['registeredMethods']);
        $this->assertCount(0, $response['availableMethods']);

        /** @var RegisteredMethod $mathMethod */
        $mathMethod = $this->objFromFixture(RegisteredMethod::class, 'robbie-math');
        $this->assertSame($mathMethod->getMethod()->getURLSegment(), $response['defaultMethod']);
    }

    /**
     * @param bool $mfaRequired
     * @param string|null $member
     * @dataProvider cannotSkipMFAProvider
     */
    public function testCannotSkipMFA($mfaRequired, $member = 'robbie')
    {
        $this->setSiteConfig(['MFARequired' => $mfaRequired]);

        if ($member) {
            $this->logInAs($member);
        }

        $response = $this->get('Security/login/default/mfa/skip');
        $this->assertContains('You cannot skip MFA registration', $response->getBody());
    }

    /**
     * @return array[]
     */
    public function cannotSkipMFAProvider()
    {
        return [
            'mfa is required' => [false],
            'mfa is not required, but user already has configured methods' => [false],
            'no member is available' => [false, null],
        ];
    }

    public function testSkipRegistration()
    {
        $this->setSiteConfig(['MFARequired' => false]);

        $member = new Member();
        $member->FirstName = 'Some new';
        $member->Surname = 'member';
        $memberId = $member->write();
        $this->logInAs($member);

        $response = $this->get('Security/login/default/mfa/skip');

        $this->assertSame(200, $response->getStatusCode());

        $member = Member::get()->byID($memberId);
        $this->assertTrue((bool)$member->HasSkippedMFARegistration);
    }

    /**
     * @expectedException \SilverStripe\MFA\Exception\MemberNotFoundException
     */
    public function testGetMemberThrowsExceptionWithoutMember()
    {
        $this->logOut();
        $handler = new LoginHandler('foo', $this->createMock(MemberAuthenticator::class));
        $handler->setRequest(new HTTPRequest('GET', '/'));
        $handler->getRequest()->setSession(new Session([]));
        $handler->getMember();
    }

    /**
     * Mark the given user as partially logged in - ie. they've entered their email/password and are currently going
     * through the MFA process
     * @param Member $member
     */
    protected function scaffoldPartialLogin(Member $member)
    {
        $this->logOut();

        $this->session()->set(SessionStore::SESSION_KEY, [
            'member' => $member->ID,
            'method' => null,
            'state' => [],
        ]);
    }

    /**
     * @param Member $member
     * @param string $password
     * @return HTTPResponse
     */
    protected function doLogin(Member $member, $password)
    {
        $this->get(Config::inst()->get(Security::class, 'login_url'));

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

    /**
     * Helper method for changing the current SiteConfig values
     *
     * @param array $data
     */
    protected function setSiteConfig(array $data)
    {
        $siteConfig = SiteConfig::current_site_config();
        $siteConfig->update($data);
        $siteConfig->write();
    }
}
