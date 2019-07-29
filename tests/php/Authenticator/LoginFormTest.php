<?php

namespace SilverStripe\MFA\Tests\Authenticator;

use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use SS_HTTPRequest as HTTPRequest;
use SS_HTTPResponse as HTTPResponse;
use Session;
use Config;
use Injector;
use FunctionalTest;
use SilverStripe\MFA\Authenticator\LoginForm;
use SilverStripe\MFA\Authenticator\MemberAuthenticator;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use MFARegisteredMethod as RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\State\Result;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method;
use SS_Datetime as DBDatetime;
use Member;
use Security;
use SecurityToken;
use SilverStripe\SecurityExtensions\Service\SudoModeServiceInterface;
use SiteConfig;

/**
 * @mixin PHPUnit_Framework_TestCase
 */
class LoginFormTest extends FunctionalTest
{
    protected static $fixture_file = 'LoginFormTest.yml';

    public function setUp()
    {
        parent::setUp();
        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(MethodRegistry::class, 'methods', [Method::class]);

        Injector::inst()->load([
            Security::class => [
                'properties' => [
                    'authenticators' => [
                        'default' => '%$' . MemberAuthenticator::class,
                    ]
                ]
            ]
        ]);

        /** @var SudoModeServiceInterface&PHPUnit_Framework_MockObject_MockObject $sudoModeService */
        $sudoModeService = $this->createMock(SudoModeServiceInterface::class);
        $sudoModeService->expects($this->any())->method('check')->willReturn(true);
        Injector::inst()->registerService($sudoModeService, SudoModeServiceInterface::class);
    }

    public function testMFAStepIsAdded()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');

        $this->autoFollowRedirection = false;
        $response = $this->doLogin($member, 'Password123');
        $this->autoFollowRedirection = true;

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith(
            Security::singleton()->Link('LoginForm/mfa'),
            $response->getHeader('Location')
        );
    }

    public function testMFARedirectsBackWhenSudoModeIsInactive()
    {
        /** @var SudoModeServiceInterface&PHPUnit_Framework_MockObject_MockObject $sudoModeService */
        $sudoModeService = $this->createMock(SudoModeServiceInterface::class);
        $sudoModeService->expects($this->once())->method('check')->willReturn(false);
        Injector::inst()->registerService($sudoModeService, SudoModeServiceInterface::class);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');
        $this->scaffoldPartialLogin($member);

        $this->autoFollowRedirection = false;
        $response = $this->get(Security::singleton()->Link('LoginForm/mfa'));

        $this->assertSame(302, $response->getStatusCode());
    }

    public function testMethodsNotBeingAvailableWillLogin()
    {
        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(MethodRegistry::class, 'methods', []);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');

        // Ensure a URL is set to redirect to after successful login
        $this->session()->set('BackURL', 'something');

        $this->autoFollowRedirection = false;
        $response = $this->doLogin($member, 'Password123');
        $this->autoFollowRedirection = true;

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('/something', $response->getHeader('Location'));
    }

    public function testMFASchemaEndpointIsNotAccessibleByDefault()
    {
        // Assert that this endpoint is not available if you haven't started the login process
        $this->autoFollowRedirection = false;
        $response = $this->get(Security::singleton()->Link('LoginForm/mfa/schema'));
        $this->autoFollowRedirection = true;

        $this->assertSame(302, $response->getStatusCode());
    }

    public function testMFASchemaEndpointReturnsMethodDetails()
    {
        // "Guy" isn't very security conscious - he has no MFA methods set up
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');
        $this->scaffoldPartialLogin($member);

        $result = $this->get(Security::singleton()->Link('LoginForm/mfa/schema'));

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

        $methods = $response['availableMethods'];
        $this->assertNotEmpty($methods);
        $firstMethod = $methods[0];

        $this->assertSame($method->getURLSegment(), $firstMethod['urlSegment']);
        $this->assertSame($method->getName(), $firstMethod['name']);
        $this->assertSame($registerHandler->getDescription(), $firstMethod['description']);
        $this->assertSame($registerHandler->getSupportLink(), $firstMethod['supportLink']);
        $this->assertContains('client/dist/images', $firstMethod['thumbnail']);
        $this->assertSame('BasicMathRegister', $firstMethod['component']);
    }

    public function testMFASchemaEndpointShowsRegisteredMethodsIfSetUp()
    {
        // "Simon" is security conscious - he uses the cutting edge MFA methods
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'simon');
        $this->scaffoldPartialLogin($member);

        $result = $this->get(Security::singleton()->Link('LoginForm/mfa/schema'));

        $response = json_decode($result->getBody(), true);

        $this->assertArrayHasKey('registeredMethods', $response);
        $this->assertArrayHasKey('availableMethods', $response);
        $this->assertArrayHasKey('defaultMethod', $response);

        $this->assertCount(1, $response['registeredMethods']);
        $this->assertCount(0, $response['availableMethods']);
        $this->assertNull($response['defaultMethod']);

        /** @var MethodInterface $method */
        $method = Injector::inst()->get(Method::class);

        $result = $response['registeredMethods'][0];
        $this->assertSame($method->getURLSegment(), $result['urlSegment']);
        $this->assertSame($method->getName(), $result['name']);
        $this->assertSame('BasicMathLogin', $result['component']);
        $this->assertSame('https://google.com', $result['supportLink']);
        $this->assertContains('totp.svg', $result['thumbnail']);
    }

    public function testMFASchemaEndpointProvidesDefaultMethodIfSet()
    {
        // "Robbie" is security conscious and is also a CMS expert! He set up MFA and set a default method :o
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'robbie');
        $this->scaffoldPartialLogin($member);

        $result = $this->get(Security::singleton()->Link('LoginForm/mfa/schema'));

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
            $this->scaffoldPartialLogin($this->objFromFixture(Member::class, $member));
        }

        $this->autoFollowRedirection = false;
        $response = $this->get(Security::singleton()->Link('LoginForm/mfa/skip'));
        $this->autoFollowRedirection = true;
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertStringEndsWith(
            Security::singleton()->Link('login'),
            $response->getHeader('Location')
        );
    }

    /**
     * @return array[]
     */
    public function cannotSkipMFAProvider()
    {
        return [
            'mfa is required' => [true],
            'mfa is not required, but user already has configured methods' => [false],
            'no member is available' => [false, null],
        ];
    }

    /**
     * @param string $memberFixture
     * @param bool $mfaRequiredInGrace
     * @param string|null $expectedRedirect
     * @dataProvider skipRegistrationProvider
     */
    public function testSkipRegistration($memberFixture, $mfaRequiredInGrace = false, $expectedRedirect = null)
    {
        if ($mfaRequiredInGrace) {
            $graceEnd = DBDatetime::now();
            $graceEnd->setValue(strtotime('+1 day', strtotime(DBDatetime::now()->getValue())));

            $this->setSiteConfig([
                'MFARequired' => true,
                'MFAGracePeriodExpires' => $graceEnd->Rfc2822()
            ]);
        } else {
            $this->setSiteConfig(['MFARequired' => false]);
        }

        if (!$expectedRedirect) {
            $expectedRedirect = '/'; // This is different to SS4
        }

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, $memberFixture);
        $this->scaffoldPartialLogin($member);

        $this->autoFollowRedirection = false;
        $response = $this->get(Security::singleton()->Link('LoginForm/mfa/skip'));

        $this->assertEquals(302, $response->getStatusCode());
        // Assert a redirect is given
        $this->assertSame(302, $response->getStatusCode());

        // Assert the redirect is to the expected location
        $this->assertStringEndsWith($expectedRedirect, $response->getHeader('Location'));

        // Assert the user is now logged in
        $this->assertSame($member->ID, Member::currentUserID(), 'User is successfully logged in');

        // Assert that the member is tracked as having skipped registration
        $member = Member::get()->byID($member->ID);
        $this->assertTrue((bool)$member->HasSkippedMFARegistration);
    }

    public function skipRegistrationProvider()
    {
        return [
            ['guy'],
            ['guy', true],
            ['pete', false, 'Security/changepassword'],
            ['pete', true, 'Security/changepassword'],
        ];
    }

    /**
     * @param string $memberFixture
     * @dataProvider methodlessMemberFixtureProvider
     */
    public function testBackURLIsPreservedWhenSkipping($memberFixture)
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, $memberFixture);
        $this->scaffoldPartialLogin($member);

        $this->doLogin($member, 'Password123', 'admin/pages');

        $this->autoFollowRedirection = false;
        $response = $this->get(Security::singleton()->Link('LoginForm/mfa/skip'));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('admin/pages', $response->getHeader('Location'));
    }

    /**
     * @expectedException \SilverStripe\MFA\Exception\MemberNotFoundException
     */
    public function testGetMemberThrowsExceptionWithoutMember()
    {
        Member::singleton()->logOut();
        $handler = new LoginForm(Security::singleton(), $this->createMock(MemberAuthenticator::class));
        $handler->setRequest(new HTTPRequest('GET', '/'));
        $handler->getMember();
    }

    public function testStartVerificationIncludesACSRFToken()
    {
        SecurityToken::enable();

        $handler = new LoginForm(Security::singleton(), $this->createMock(MemberAuthenticator::class));
        $member = $this->objFromFixture(Member::class, 'robbie');
        $store = new SessionStore($member);
        $handler->setStore($store);

        $request = new HTTPRequest('GET', '/');
        $request->setRouteParams(['Method' => 'basic-math']);
        $handler->setRequest($request);

        $response = json_decode($handler->startVerification()->getBody());

        $this->assertNotNull($response->SecurityID);
        $this->assertTrue(SecurityToken::inst()->check($response->SecurityID));
    }

    public function testVerifyAssertsValidCSRFToken()
    {
        SecurityToken::enable();

        $handler = new LoginForm(Security::singleton(), $this->createMock(MemberAuthenticator::class));
        $member = $this->objFromFixture(Member::class, 'robbie');
        $store = new SessionStore($member);
        $store->setMethod('basic-math');
        $handler->setStore($store);

        $request = new HTTPRequest('GET', '/');
        $handler->setRequest($request);

        $response = $handler->finishVerification();

        $this->assertSame(403, $response->getStatusCode());
        $this->assertContains('Your request timed out', $response->getBody());

        $request = new HTTPRequest('GET', '/', [
            SecurityToken::inst()->getName() => SecurityToken::inst()->getValue()
        ]);
        $handler->setRequest($request);

        // Mock the verification process...
        $mockVerifyHandler = $this->createMock(VerifyHandlerInterface::class);
        $mockRegisteredMethod = $this->createMock(RegisteredMethod::class);
        $mockRegisteredMethodManager = $this->createMock(RegisteredMethodManager::class);

        $mockRegisteredMethodManager
            ->expects($this->once())->method('getFromMember')->willReturn($mockRegisteredMethod);
        $mockRegisteredMethod->expects($this->once())->method('getVerifyHandler')->willReturn($mockVerifyHandler);
        $mockVerifyHandler->expects($this->once())->method('verify')->willReturn(Result::create());

        // Register our mock service
        Injector::inst()->registerService($mockRegisteredMethodManager, RegisteredMethodManager::class);

        $response = $handler->finishVerification();

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testStartVerificationReturnsForbiddenWithoutMember()
    {
        Member::singleton()->logOut();

        $handler = new LoginForm(Security::singleton(), $this->createMock(MemberAuthenticator::class));
        $handler->setRequest(new HTTPRequest('GET', '/'));
        $this->session()->inst_start(new Session([]));
        $handler->setStore($this->createMock(StoreInterface::class));

        $response = $handler->startVerification($handler->getRequest());
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testStartVerificationReturnsForbiddenWithoutSudoMode()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'robbie');
        $this->scaffoldPartialLogin($member);

        /** @var SudoModeServiceInterface&PHPUnit_Framework_MockObject_MockObject $sudoModeService */
        $sudoModeService = $this->createMock(SudoModeServiceInterface::class);
        $sudoModeService->method('check')->willReturn(false);
        Injector::inst()->registerService($sudoModeService, SudoModeServiceInterface::class);

        $handler = new LoginForm(Security::singleton(), $this->createMock(MemberAuthenticator::class));
        $handler->setRequest(new HTTPRequest('GET', '/'));
        $this->session()->inst_start(new Session([]));

        $store = new SessionStore($member);
        $store->setMethod('basic-math');
        $handler->setStore($store);

        $response = $handler->startVerification($handler->getRequest());
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testFinishVerificationHandlesMembersLockedOut()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'robbie');
        // Mock the member being locked out for fifteen minutes
        $member->LockedOutUntil = date('Y-m-d H:i:s', intval(DBDatetime::now()->Format('U')) + 15 * 60);
        $member->write();

        $handler = new LoginForm(Security::singleton(), $this->createMock(MemberAuthenticator::class));
        $request = new HTTPRequest('GET', '/');
        $this->session()->inst_start(new Session([]));

        $store = new SessionStore($member);
        $store->setMethod('basic-math');
        $handler->setStore($store);

        $response = $handler->finishVerification($request);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertContains('Your account is temporarily locked', (string) $response->getBody());
    }

    public function testFinishVerificationChecksSudoModeIsActive()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'robbie');

        $handler = new LoginForm(Security::singleton(), $this->createMock(MemberAuthenticator::class));
        $request = new HTTPRequest('GET', '/');
        $this->session()->inst_start(new Session([]));

        $store = new SessionStore($member);
        $store->setMethod('basic-math');
        $handler->setStore($store);

        /** @var SudoModeServiceInterface&PHPUnit_Framework_MockObject_MockObject $sudoModeService */
        $sudoModeService = $this->createMock(SudoModeServiceInterface::class);
        $sudoModeService->method('check')->willReturn(false);
        Injector::inst()->registerService($sudoModeService, SudoModeServiceInterface::class);

        $response = $handler->finishVerification($request);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertContains('You need to re-verify your account before continuing', (string) $response->getBody());
    }

    public function testFinishVerificationPassesExceptionMessagesThroughFromMethodsWithValidationFailures()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'robbie');
        Config::inst()->update(Member::class, 'lock_out_after_incorrect_logins', 5);
        $failedLogins = $member->FailedLoginCount;

        /** @var LoginForm|PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(LoginForm::class)
            ->setMethods(['completeVerificationRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $handler->setController(Security::singleton());

        $handler->expects($this->once())->method('completeVerificationRequest')->willReturn(
            Result::create(false, 'It failed because it\'s mocked, obviously')
        );

        $request = new HTTPRequest('GET', '/');
        $handler->setRequest($request);
        $this->session()->inst_start(new Session([]));
        $store = new SessionStore($member);
        $store->setMethod('basic-math');
        $handler->setStore($store);

        $response = $handler->finishVerification();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertContains('It failed because it\'s mocked', (string) $response->getBody());
        $this->assertSame($failedLogins + 1, $member->FailedLoginCount, 'Failed login is registered');
    }

    /**
     * @param string $memberFixture
     * @dataProvider methodlessMemberFixtureProvider
     */
    public function testFinishVerificationWillRedirectToTheBackURLSetAsLoginIsStarted($memberFixture)
    {
        // todo Fix this for SilverStripe 3
        $this->markTestSkipped('This test fails in SilverStripe 3, needs to be rewritten. Subject works in practice.');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, $memberFixture);
        $this->scaffoldPartialLogin($member);

        $this->doLogin($member, 'Password123', 'admin/pages');

        /** @var LoginForm|PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(LoginForm::class)
            ->setMethods(['completeVerificationRequest'])
            ->disableOriginalConstructor()
            ->getMock();

        $handler->expects($this->once())->method('completeVerificationRequest')->willReturn(Result::create());

        $request = new HTTPRequest('GET', '/');
        $handler->setRequest($request);

        $store = new SessionStore($member);
        $store->setMethod('basic-math');
        $handler->setStore($store);

        $session = new Session([]);
        $handler->getController()->setSession($session);

        $response = $handler->finishVerification();

        // Assert "Accepted" response
        $this->assertSame(202, $response->getStatusCode());

        $this->autoFollowRedirection = false;
        $response = $this->get(Security::singleton()->Link('LoginForm/mfa/complete'));
        $this->assertSame(302, $response->getStatusCode());
        $this->assertStringEndsWith('admin/pages', $response->getHeader('location'));
    }

    public function testGetBackURL()
    {
        $handler = new LoginForm(Security::singleton(), $this->createMock(MemberAuthenticator::class));

        $request = new HTTPRequest('GET', '/');
        $handler->setRequest($request);

        $session = new Session([]);
        $session->set(LoginForm::SESSION_KEY . '.additionalData', ['BackURL' => 'foobar']);
        $handler->getController()->setSession($session);

        $this->assertSame('foobar', $handler->getBackURL());
    }

    public function methodlessMemberFixtureProvider()
    {
        return [['guy', 'carla']];
    }

    /**
     * Mark the given user as partially logged in - ie. they've entered their email/password and are currently going
     * through the MFA process
     * @param Member $member
     */
    protected function scaffoldPartialLogin(Member $member)
    {
        Member::singleton()->logOut();

        $this->session()->set(SessionStore::SESSION_KEY, new SessionStore($member));
    }

    /**
     * @param Member $member
     * @param string $password
     * @return HTTPResponse
     */
    protected function doLogin(Member $member, $password, $backUrl = null)
    {
        $url = Security::login_url();

        if ($backUrl) {
            $url .= '?BackURL=' . $backUrl;
        }

        $this->get($url);

        return $this->submitForm(
            'SilverStripe_MFA_Authenticator_LoginForm_LoginForm',
            null,
            [
                'Email' => $member->Email,
                'Password' => $password,
                'AuthenticationMethod' => MemberAuthenticator::class,
                'action_doLogin' => 1,
            ]
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
