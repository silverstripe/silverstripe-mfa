<?php

namespace SilverStripe\MFA\Tests\Controller;

use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use SilverStripe\Admin\AdminRootController;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\Middleware\HTTPCacheControlMiddleware;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Controller\AdminRegistrationController;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\State\AvailableMethodDetails;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\Core\Validation\ValidationException;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;
use SilverStripe\Security\SecurityToken;
use SilverStripe\Security\SudoMode\SudoModeServiceInterface;

class AdminRegistrationControllerTest extends FunctionalTest
{
    protected static $fixture_file = 'AdminRegistrationControllerTest.yml';

    protected function setUp(): void
    {
        parent::setUp();

        MethodRegistry::config()->set('methods', [
            BasicMathMethod::class,
        ]);

        /** @var SudoModeServiceInterface&MockObject $sudoModeService */
        $sudoModeService = $this->createMock(SudoModeServiceInterface::class);
        $sudoModeService->expects($this->any())->method('check')->willReturn(true);
        Injector::inst()->registerService($sudoModeService, SudoModeServiceInterface::class);
    }

    public function testStartRegistrationAssertsValidMethod()
    {
        $this->logInAs($this->objFromFixture(Member::class, 'sally_smith'));

        $result = $this->get(Controller::join_links(AdminRootController::admin_url(), 'mfa', 'register/foo'));

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString('No such method is available', $result->getBody());
    }

    public function testStartRegistrationEnforcesSudoMode()
    {
        $this->logInAs($this->objFromFixture(Member::class, 'sally_smith'));

        /** @var SudoModeServiceInterface&MockObject $sudoModeService */
        $sudoModeService = $this->createMock(SudoModeServiceInterface::class);
        $sudoModeService->expects($this->any())->method('check')->willReturn(false);
        Injector::inst()->registerService($sudoModeService, SudoModeServiceInterface::class);

        $result = $this->get(Controller::join_links(AdminRootController::admin_url(), 'mfa', 'register/foo'));

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString(
            'Invalid session. Please refresh and try again.',
            (string) $result->getBody()
        );
    }

    public function testStartRegistrationReturns200Response()
    {
        $this->logInAs($this->objFromFixture(Member::class, 'sally_smith'));
        $method = new BasicMathMethod();

        $result = $this->get(
            Controller::join_links(
                AdminRootController::admin_url(),
                'mfa',
                'register',
                $method->getURLSegment()
            )
        );

        $this->assertSame(200, $result->getStatusCode());
    }

    /**
     * Test that HTTP caching is disabled in requests to the registration endpoint
     */
    public function testStartRegistrationDisablesHTTPCaching()
    {
        $this->logInAs($this->objFromFixture(Member::class, 'sally_smith'));
        $middleware = HTTPCacheControlMiddleware::singleton();
        $middleware->enableCache(true);
        $this->assertSame('enabled', $middleware->getState());

        $this->get(Controller::join_links(AdminRootController::admin_url(), 'mfa', 'register/foo'));
        $this->assertSame('disabled', $middleware->getState());
    }

    public function testFinishRegistrationGracefullyHandlesInvalidSessions()
    {
        $this->logInAs($this->objFromFixture(Member::class, 'sally_smith'));
        $method = new BasicMathMethod();

        $result = $this->post(
            Controller::join_links(
                AdminRootController::admin_url(),
                'mfa',
                'register',
                $method->getURLSegment()
            ),
            ['dummy' => 'data']
        );

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString('Invalid session', $result->getBody());
    }

    public function testFinishRegistrationAssertsValidMethod()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->logInAs($member);
        $method = new BasicMathMethod();

        $store = new SessionStore($member);
        $store->setMethod($method->getURLSegment());
        $this->session()->set(SessionStore::SESSION_KEY, $store);

        $result = $this->post(
            Controller::join_links(
                AdminRootController::admin_url(),
                'mfa',
                'register',
                'foo'
            ),
            ['dummy' => 'data']
        );

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString('No such method is available', $result->getBody());
    }

    public function testFinishRegistrationCompletesWhenValid()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->logInAs($member);
        $method = new BasicMathMethod();

        $store = new SessionStore($member);
        $store->setMethod($method->getURLSegment());
        $this->session()->set(SessionStore::SESSION_KEY, $store);

        $result = $this->post(
            Controller::join_links(
                AdminRootController::admin_url(),
                'mfa',
                'register',
                $method->getURLSegment()
            ),
            ['dummy' => 'data'],
            null,
            $this->session(),
            json_encode(['number' => 7])
        );

        $this->assertSame(201, $result->getStatusCode());
    }

    public function testRemoveRegistrationChecksCSRF()
    {
        SecurityToken::enable();

        $controller = new AdminRegistrationController();
        $request = new HTTPRequest('DELETE', '');
        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Request timed out', $response->getBody());

        $token = SecurityToken::inst();
        $request = new HTTPRequest('DELETE', '', [$token->getName() => $token->getValue()]);

        $response = $controller->removeRegisteredMethod($request);

        $this->assertStringNotContainsString('Request timed out', $response->getBody());
    }

    public function testRemoveRegistrationRequiresMethod()
    {
        $this->logInWithPermission();

        // Prep a mock for deleting methods
        $registeredMethodManager = $this->scaffoldRegisteredMethodManagerMock();

        $controller = new AdminRegistrationController();

        // Method not even provided
        $request = new HTTPRequest('DELETE', '');
        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('No such method is available', $response->getBody());

        // Method provided but non-existing
        $request = new HTTPRequest('DELETE', '');
        $request->setRouteParams(['Method' => 'fake123']);
        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('No such method is available', $response->getBody());

        // Existing method
        $request = new HTTPRequest('DELETE', '');
        $basicMathMethod = new BasicMathMethod();
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);
        $registeredMethodManager->expects($this->once())->method('deleteFromMember')->willReturn(true);
        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(json_decode($response->getBody())->success);
    }

    public function testRemoveRegistrationSuccessIsReflectedInResponse()
    {
        $this->logInWithPermission();

        // Prep a mock for deleting methods
        $registeredMethodManager = $this->scaffoldRegisteredMethodManagerMock();

        $controller = new AdminRegistrationController();

        $request = new HTTPRequest('DELETE', '');
        $basicMathMethod = new BasicMathMethod();
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);

        $registeredMethodManager->expects($this->exactly(2))->method('deleteFromMember')->willReturn(true, false);

        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(json_decode($response->getBody())->success);

        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Could not delete the specified method from the user', $response->getBody());
    }

    public function testRemoveRegistrationSuccessResponseIncludesTheNowAvailableMethod()
    {
        $this->logInWithPermission();

        // Prep a mock for deleting methods
        $registeredMethodManager = $this->scaffoldRegisteredMethodManagerMock();

        $controller = new AdminRegistrationController();

        $request = new HTTPRequest('DELETE', '');
        $basicMathMethod = new BasicMathMethod();
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);
        $registeredMethodManager->expects($this->once())->method('deleteFromMember')->willReturn(true);

        $expectedAvailableMethod = new AvailableMethodDetails($basicMathMethod);

        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(
            $expectedAvailableMethod->jsonSerialize(),
            json_decode($response->getBody(), true)['availableMethod']
        );
    }

    public function testRemoveRegistrationSuccessIndicatesIfTheBackupMethodIsRegistered()
    {
        $this->logInWithPermission();

        // Prep a mock for deleting methods
        $registeredMethodManager = $this->scaffoldRegisteredMethodManagerMock();

        $controller = new AdminRegistrationController();

        $request = new HTTPRequest('DELETE', '');
        $basicMathMethod = new BasicMathMethod();
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);
        $registeredMethodManager->expects($this->any())->method('deleteFromMember')->willReturn(true);

        // Test when there's no backup method registered
        Config::modify()->set(MethodRegistry::class, 'default_backup_method', null);
        $response = $controller->removeRegisteredMethod($request);
        $this->assertFalse(json_decode($response->getBody())->hasBackupMethod);

        // Make "basic math" the backup method as it's the only available method
        Config::modify()->set(MethodRegistry::class, 'default_backup_method', BasicMathMethod::class);

        // Mock checking for the registered backup method when it's not registered (first) and then when it is (second)
        $registeredMethodManager
            ->expects($this->exactly(2))
            ->method('getFromMember')
            ->willReturn(null, new RegisteredMethod());

        $response = $controller->removeRegisteredMethod($request);
        $this->assertFalse(json_decode($response->getBody())->hasBackupMethod);
        $response = $controller->removeRegisteredMethod($request);
        $this->assertTrue(json_decode($response->getBody())->hasBackupMethod);
    }

    protected function scaffoldRegisteredMethodManagerMock()
    {
        $mock = $this->createMock(RegisteredMethodManager::class);
        Injector::inst()->registerService($mock, RegisteredMethodManager::class);

        return $mock;
    }

    public function testEnforcesSudoMode()
    {
        $sudoModeService = $this->createMock(SudoModeServiceInterface::class);
        $sudoModeService->expects($this->any())->method('check')->willReturn(false);
        Injector::inst()->registerService($sudoModeService, SudoModeServiceInterface::class);

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->logInAs($member);
        $method = new BasicMathMethod();

        $store = new SessionStore($member);
        $store->setMethod($method->getURLSegment());
        $this->session()->set(SessionStore::SESSION_KEY, $store);

        $result = $this->post(
            Controller::join_links(
                AdminRootController::admin_url(),
                'mfa',
                'register',
                $method->getURLSegment()
            ),
            ['dummy' => 'data'],
            null,
            $this->session(),
            json_encode(['number' => 7])
        );

        $this->assertSame(400, $result->getStatusCode());
        $this->assertStringContainsString('Invalid session', $result->getBody());
    }

    /**
     * This controller allows any logged in user to access it, since its methods have their own permission
     * check validation already.
     *
     * See: https://github.com/silverstripe/silverstripe-mfa/issues/171
     */
    public function testAnyUserCanView()
    {
        $this->assertFalse(AdminRegistrationController::getRequiredPermissions());
    }

    public function testSetDefaultRegisteredMethodChecksCSRF()
    {
        SecurityToken::enable();

        $request = new HTTPRequest('POST', '');
        $request->setSession($this->session());
        $controller = new AdminRegistrationController();

        $response = $controller->setDefaultRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Request timed out', $response->getBody());

        $token = SecurityToken::inst();
        $request = new HTTPRequest('POST', '', [$token->getName() => $token->getValue()]);
        $request->setSession($this->session());

        $response = $controller->setDefaultRegisteredMethod($request);
        $this->assertStringNotContainsString('Request timed out', $response->getBody());
    }

    public function testSetDefaultRegisteredMethodFailsWhenMethodWasNotFound()
    {
        $request = new HTTPRequest('POST', '');
        $request->setRouteParams(['Method' => 'doesnotexist']);
        $request->setSession($this->session());
        $controller = new AdminRegistrationController();

        $response = $controller->setDefaultRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('No such method is available', $response->getBody());
    }

    public function testSetDefaultRegisteredMethodFailsWhenRegisteredMethodWasNotFoundForUser()
    {
        // Arbitrary user, no MFA configured for it
        $this->logInWithPermission();

        $request = new HTTPRequest('POST', '');
        $basicMathMethod = new BasicMathMethod();
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);
        $request->setSession($this->session());
        $controller = new AdminRegistrationController();

        $response = $controller->setDefaultRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('No such registered method is available', $response->getBody());
    }

    public function testSetDefaultRegisteredMethodHandlesExceptionsOnWrite()
    {
        $registeredMethodManager = $this->scaffoldRegisteredMethodManagerMock();

        $controller = new AdminRegistrationController();
        /** @var LoggerInterface&MockObject $loggerMock */
        $loggerMock = $this->createMock(LoggerInterface::class);
        $controller->setLogger($loggerMock);

        $request = new HTTPRequest('POST', '');
        $request->setSession($this->session());
        $basicMathMethod = new BasicMathMethod();
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);

        $registeredMethodManager
            ->expects($this->once())
            ->method('getFromMember')
            ->willReturn(new RegisteredMethod());

        /** @var Member&MockObject $memberMock */
        $memberMock = $this->createMock(Member::class);
        $memberMock->method('write')->willThrowException(new ValidationException());
        Security::setCurrentUser($memberMock);

        $loggerMock->expects($this->once())->method('debug');
        $response = $controller->setDefaultRegisteredMethod($request);
        $this->assertSame(400, $response->getStatusCode());
        $this->assertStringContainsString('Could not set the default method for the user', $response->getBody());
    }

    public function testSetDefaultRegisteredMethod()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $this->logInAs($member);
        // Give Sally basic math
        $basicMathMethod = new BasicMathMethod();
        RegisteredMethodManager::singleton()->registerForMember($member, $basicMathMethod, ['foo' => 'bar']);

        // Set basic math as the default method
        $controller = new AdminRegistrationController();
        $request = new HTTPRequest('POST', '');
        $request->setSession($this->session());
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);

        $response = $controller->setDefaultRegisteredMethod($request);
        $this->assertSame(200, $response->getStatusCode());
    }
}
