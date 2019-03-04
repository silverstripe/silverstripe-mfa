<?php

namespace SilverStripe\MFA\Tests\Authenticator;

use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Authenticator\MemberAuthenticator;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class LoginHandlerTest extends FunctionalTest
{
    protected static $fixture_file = 'LoginHandlerTest.yml';

    protected function setUp()
    {
        parent::setUp();
        Config::modify()->set(MethodRegistry::class, 'methods', [Method::class]);

        Injector::inst()->load([
            'SilverStripe\Security\Security' => [
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
        $member = $this->objFromFixture(Member::class, 'guy');

        $this->autoFollowRedirection = false;
        $response = $this->doLogin($member, 'Password123');
        $this->autoFollowRedirection = true;

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/Security/login/default/mfa', $response->getHeader('location'));
    }
    
    public function testMethodsNotBeingAvailableWillLogin()
    {
        Config::modify()->set(MethodRegistry::class, 'methods', []);

        $member = $this->objFromFixture(Member::class, 'guy');

        // Ensure a URL is set to redirect to after successful login
        $this->session()->set('BackURL', 'something');

        $this->autoFollowRedirection = false;
        $response = $this->doLogin($member, 'Password123');
        $this->autoFollowRedirection = true;

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame('http://localhost/something', $response->getHeader('location'));
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
        $member = $this->objFromFixture(Member::class, 'guy');
        $this->scaffoldPartialLogin($member);

        $result = $this->get('Security/login/default/mfa/schema');

        $response = json_decode($result->getBody(), true);

        $this->assertArrayHasKey('registeredMethods', $response);
        $this->assertArrayHasKey('registrationDetails', $response);
        $this->assertArrayHasKey('defaultMethod', $response);

        $this->assertCount(0, $response['registeredMethods']);
        $this->assertCount(1, $response['registrationDetails']);
        $this->assertNull($response['defaultMethod']);

        /** @var MethodInterface $method */
        $method = Injector::inst()->get(Method::class);
        $registerHandler = $method->getRegisterHandler();

        $this->assertSame([$method->getURLSegment() => [
            'name' => $registerHandler->getName(),
            'description' => $registerHandler->getDescription(),
            'supportLink' => $registerHandler->getSupportLink(),
        ]], $response['registrationDetails']);
    }

    public function testMFASchemaEndpointShowsRegisteredMethodsIfSetUp()
    {
        // "Simon" is security conscious - he uses the cutting edge MFA methods
        $member = $this->objFromFixture(Member::class, 'simon');
        $this->scaffoldPartialLogin($member);

        $result = $this->get('Security/login/default/mfa/schema');

        $response = json_decode($result->getBody(), true);

        $this->assertArrayHasKey('registeredMethods', $response);
        $this->assertArrayHasKey('registrationDetails', $response);
        $this->assertArrayHasKey('defaultMethod', $response);

        $this->assertCount(1, $response['registeredMethods']);
        $this->assertCount(0, $response['registrationDetails']);
        $this->assertNull($response['defaultMethod']);

        /** @var MethodInterface $method */
        $method = Injector::inst()->get(Method::class);
        $loginHandler = $method->getLoginHandler();

        $this->assertSame(
            [$method->getURLSegment() => $loginHandler->getLeadInLabel()],
            $response['registeredMethods']
        );
    }

    public function testMFASchemaEndpointProvidesDefaultMethodIfSet()
    {
        // "Robbie" is security conscious and is also a CMS expert! He set up MFA and set a default method :o
        $member = $this->objFromFixture(Member::class, 'robbie');
        $this->scaffoldPartialLogin($member);

        $result = $this->get('Security/login/default/mfa/schema');

        $response = json_decode($result->getBody(), true);

        $this->assertArrayHasKey('registeredMethods', $response);
        $this->assertArrayHasKey('registrationDetails', $response);
        $this->assertArrayHasKey('defaultMethod', $response);

        $this->assertCount(1, $response['registeredMethods']);
        $this->assertCount(0, $response['registrationDetails']);

        /** @var RegisteredMethod $mathMethod */
        $mathMethod = $this->objFromFixture(RegisteredMethod::class, 'robbie-math');
        $this->assertSame($mathMethod->getMethod()->getURLSegment(), $response['defaultMethod']);
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
            "MemberLoginForm_LoginForm",
            null,
            array(
                'Email' => $member->Email,
                'Password' => $password,
                'AuthenticationMethod' => MemberAuthenticator::class,
                'action_doLogin' => 1,
            )
        );
    }
}