<?php

namespace SilverStripe\MFA\Tests\Controller;

use PHPUnit_Framework_MockObject_MockObject;
 use Psr\Log\LoggerInterface; // Not present in SS3
use AdminRootController;
use Controller;
use SS_HTTPRequest as HTTPRequest;
use Config;
use Injector;
use FunctionalTest;
use SilverStripe\MFA\Controller\AdminRegistrationController;
use SilverStripe\MFA\Extension\MemberExtension;
use MFARegisteredMethod as RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\State\AvailableMethodDetails;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SS_Log;
use ValidationException;
use Member;
use Security;
use SecurityToken;
use SilverStripe\SecurityExtensions\Service\SudoModeServiceInterface;

class AdminRegistrationControllerTest extends FunctionalTest
{
    protected static $fixture_file = 'AdminRegistrationControllerTest.yml';

    public function setUp()
    {
        parent::setUp();

        Config::inst()->remove(MethodRegistry::class, 'methods');
        Config::inst()->update(MethodRegistry::class, 'methods', [BasicMathMethod::class]);

        /** @var SudoModeServiceInterface&PHPUnit_Framework_MockObject_MockObject $sudoModeService */
        $sudoModeService = $this->createMock(SudoModeServiceInterface::class);
        $sudoModeService->expects($this->any())->method('check')->willReturn(true);
        Injector::inst()->registerService($sudoModeService, SudoModeServiceInterface::class);
    }

    public function testStartRegistrationAssertsValidMethod()
    {
        $this->objFromFixture(Member::class, 'sally_smith')->logIn();

        $result = $this->get(Controller::join_links('admin', 'mfa', 'register/foo'));

        $this->assertSame(400, $result->getStatusCode());
        $this->assertContains('No such method is available', $result->getBody());
    }

    public function testStartRegistrationEnforcesSudoMode()
    {
        $this->objFromFixture(Member::class, 'sally_smith')->logIn();

        /** @var SudoModeServiceInterface&PHPUnit_Framework_MockObject_MockObject $sudoModeService */
        $sudoModeService = $this->createMock(SudoModeServiceInterface::class);
        $sudoModeService->expects($this->any())->method('check')->willReturn(false);
        Injector::inst()->registerService($sudoModeService, SudoModeServiceInterface::class);

        $result = $this->get(Controller::join_links('admin', 'mfa', 'register/foo'));

        $this->assertSame(400, $result->getStatusCode());
        $this->assertContains('Invalid session. Please refresh and try again.', (string) $result->getBody());
    }

    public function testStartRegistrationReturns200Response()
    {
        $this->objFromFixture(Member::class, 'sally_smith')->logIn();
        $method = new BasicMathMethod();

        $result = $this->get(
            Controller::join_links(
                'admin',
                'mfa',
                'register',
                $method->getURLSegment()
            )
        );

        $this->assertSame(200, $result->getStatusCode());
    }

    public function testFinishRegistrationGracefullyHandlesInvalidSessions()
    {
        $this->objFromFixture(Member::class, 'sally_smith')->logIn();
        $method = new BasicMathMethod();

        $result = $this->post(
            Controller::join_links(
                'admin',
                'mfa',
                'register',
                $method->getURLSegment()
            ),
            ['dummy' => 'data']
        );

        $this->assertSame(400, $result->getStatusCode());
        $this->assertContains('Invalid session', $result->getBody());
    }

    public function testFinishRegistrationAssertsValidMethod()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->logIn();
        $method = new BasicMathMethod();

        $store = new SessionStore($member);
        $store->setMethod($method->getURLSegment());
        $this->session()->set(SessionStore::SESSION_KEY, $store);

        $result = $this->post(
            Controller::join_links(
                'admin',
                'mfa',
                'register',
                'foo'
            ),
            ['dummy' => 'data']
        );

        $this->assertSame(400, $result->getStatusCode());
        $this->assertContains('No such method is available', $result->getBody());
    }

    public function testFinishRegistrationCompletesWhenValid()
    {
        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->logIn();
        $method = new BasicMathMethod();

        $store = new SessionStore($member);
        $store->setMethod($method->getURLSegment());
        $this->session()->set(SessionStore::SESSION_KEY, $store);

        $result = $this->post(
            Controller::join_links(
                'admin',
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

        $controller = AdminRegistrationController::singleton();
        $request = new HTTPRequest('DELETE', '');
        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertContains('Request timed out', $response->getBody());

        $token = SecurityToken::inst();
        $request = new HTTPRequest('DELETE', '', [$token->getName() => $token->getValue()]);

        $response = $controller->removeRegisteredMethod($request);

        $this->assertNotContains('Request timed out', $response->getBody());
    }

    public function testRemoveRegistrationRequiresMethod()
    {
        $this->logInWithPermission();

        // Prep a mock for deleting methods
        $registeredMethodManager = $this->scaffoldRegisteredMethodManagerMock();

        $controller = AdminRegistrationController::singleton();

        // Method not even provided
        $request = new HTTPRequest('DELETE', '');
        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertContains('No such method is available', $response->getBody());

        // Method provided but non-existing
        $request = new HTTPRequest('DELETE', '');
        $request->setRouteParams(['Method' => 'fake123']);
        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertContains('No such method is available', $response->getBody());

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

        $controller = AdminRegistrationController::singleton();

        $request = new HTTPRequest('DELETE', '');
        $basicMathMethod = new BasicMathMethod();
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);

        $registeredMethodManager->expects($this->exactly(2))->method('deleteFromMember')->willReturn(true, false);

        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue(json_decode($response->getBody())->success);

        $response = $controller->removeRegisteredMethod($request);

        $this->assertSame(400, $response->getStatusCode());
        $this->assertContains('Could not delete the specified method from the user', $response->getBody());
    }

    public function testRemoveRegistrationSuccessResponseIncludesTheNowAvailableMethod()
    {
        $this->logInWithPermission();

        // Prep a mock for deleting methods
        $registeredMethodManager = $this->scaffoldRegisteredMethodManagerMock();

        $controller = AdminRegistrationController::singleton();

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

        $controller = AdminRegistrationController::singleton();

        $request = new HTTPRequest('DELETE', '');
        $basicMathMethod = new BasicMathMethod();
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);
        $registeredMethodManager->expects($this->any())->method('deleteFromMember')->willReturn(true);

        // Test when there's no backup method registered
        Config::inst()->remove(MethodRegistry::class, 'default_backup_method');
        Config::inst()->update(MethodRegistry::class, 'default_backup_method', null);
        $response = $controller->removeRegisteredMethod($request);
        $this->assertFalse(json_decode($response->getBody())->hasBackupMethod);

        // Make "basic math" the backup method as it's the only available method
        Config::inst()->remove(MethodRegistry::class, 'default_backup_method');
        Config::inst()->update(MethodRegistry::class, 'default_backup_method', BasicMathMethod::class);

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
        $member->logIn();
        $method = new BasicMathMethod();

        $store = new SessionStore($member);
        $store->setMethod($method->getURLSegment());
        $this->session()->set(SessionStore::SESSION_KEY, $store);

        $result = $this->post(
            Controller::join_links(
                'admin',
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
        $this->assertContains('Invalid session', $result->getBody());
    }

    /**
     * This controller allows any logged in user to access it, since its methods have their own permission
     * check validation already.
     *
     * See: https://github.com/silverstripe/silverstripe-mfa/issues/171
     */
    public function testAnyUserCanView()
    {
        $this->objFromFixture(Member::class, 'sally_smith')->logIn();

        $this->assertTrue(AdminRegistrationController::singleton()->canView());
    }

    public function testSetDefaultRegisteredMethodChecksCSRF()
    {
        SecurityToken::enable();

        $controller = AdminRegistrationController::singleton();
        $request = new HTTPRequest('POST', '');
        $controller->setSession($this->session());
        $controller->setRequest($request);

        $response = $controller->setDefaultRegisteredMethod();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertContains('Request timed out', $response->getBody());

        $token = SecurityToken::inst();
        $request = new HTTPRequest('POST', '', [$token->getName() => $token->getValue()]);
        $controller->setSession($this->session());
        $controller->setRequest($request);

        $response = $controller->setDefaultRegisteredMethod();
        $this->assertNotContains('Request timed out', $response->getBody());
    }

    public function testSetDefaultRegisteredMethodFailsWhenMethodWasNotFound()
    {
        $controller = AdminRegistrationController::singleton();
        $request = new HTTPRequest('POST', '');
        $request->setRouteParams(['Method' => 'doesnotexist']);
        $controller->setRequest($request);
        $controller->setSession($this->session());

        $response = $controller->setDefaultRegisteredMethod();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertContains('No such method is available', $response->getBody());
    }

    public function testSetDefaultRegisteredMethodFailsWhenRegisteredMethodWasNotFoundForUser()
    {
        // Arbitrary user, no MFA configured for it
        $this->logInWithPermission();

        $controller = AdminRegistrationController::singleton();
        $request = new HTTPRequest('POST', '');
        $basicMathMethod = new BasicMathMethod();
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);
        $controller->setRequest($request);
        $controller->setSession($this->session());

        $response = $controller->setDefaultRegisteredMethod();

        $this->assertSame(400, $response->getStatusCode());
        $this->assertContains('No such registered method is available', $response->getBody());
    }

    public function testSetDefaultRegisteredMethod()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'sally_smith');
        $member->logIn();
        // Give Sally basic math
        $basicMathMethod = new BasicMathMethod();
        RegisteredMethodManager::singleton()->registerForMember($member, $basicMathMethod, ['foo' => 'bar']);

        // Set basic math as the default method
        $controller = AdminRegistrationController::singleton();
        $request = new HTTPRequest('POST', '');
        $request->setRouteParams(['Method' => $basicMathMethod->getURLSegment()]);
        $controller->setRequest($request);
        $controller->setSession($this->session());

        $response = $controller->setDefaultRegisteredMethod();
        $this->assertSame(200, $response->getStatusCode());
    }
}
