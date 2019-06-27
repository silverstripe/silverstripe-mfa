<?php

namespace SilverStripe\MFA\Tests\Authenticator;

use PHPUnit_Framework_MockObject_MockObject;
use Psr\Log\LoggerInterface;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Session;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Authenticator\ChangePasswordHandler;
use SilverStripe\MFA\Authenticator\MemberAuthenticator;
use SilverStripe\MFA\Exception\InvalidMethodException;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\State\Result;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\MFA\Store\StoreInterface;
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

    public function testGetSchema()
    {
        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'robbie');
        $memberId = $member->ID;
        $token = $member->generateAutologinTokenAndStoreHash();
        $this->get("Security/changepassword?m={$memberId}&t={$token}");

        $response = $this->get('Security/changepassword/mfa/schema');

        $this->assertSame('application/json', $response->getHeader('Content-Type'));
        $schema = json_decode($response->getBody(), true);

        $this->assertArrayHasKey('endpoints', $schema);
        $this->assertArrayHasKey('verify', $schema['endpoints']);
        $this->assertArrayHasKey('complete', $schema['endpoints']);
        $this->assertFalse($schema['shouldRedirect']);
    }

    public function testMfaRedirectsBackWithoutMember()
    {
        $this->autoFollowRedirection = false;
        $response = $this->get('Security/changepassword/mfa');

        $this->assertEquals(302, $response->getStatusCode());
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Store not found, please create one first.
     */
    public function testStartMfaCheckThrowsExceptionWithoutStore()
    {
        /** @var ChangePasswordHandler&PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(ChangePasswordHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();

        $handler->expects($this->once())->method('getStore')->willReturn(null);

        $handler->startMFACheck($this->createMock(HTTPRequest::class));
    }

    public function testStartMfaReturnsForbiddenWithoutMember()
    {
        /** @var ChangePasswordHandler&PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(ChangePasswordHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore'])
            ->getMock();

        $store = $this->createMock(StoreInterface::class);

        $handler->expects($this->once())->method('getStore')->willReturn($store);
        $store->expects($this->once())->method('getMember')->willReturn(null);

        $response = $handler->startMFACheck($this->createMock(HTTPRequest::class));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testMfaStartsVerificationResponse()
    {
        /** @var ChangePasswordHandler&PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(ChangePasswordHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'createStartVerificationResponse'])
            ->getMock();

        $store = $this->createMock(StoreInterface::class);

        $handler->expects($this->once())->method('getStore')->willReturn($store);
        $store->expects($this->once())->method('getMember')->willReturn($this->createMock(Member::class));

        $expectedResponse = new HTTPResponse();
        $handler->expects($this->once())->method('createStartVerificationResponse')->willReturn($expectedResponse);

        $request = new HTTPRequest('GET', '');
        $request->setRouteParams(['Method' => 'test']);
        $response = $handler->startMFACheck($request);
        $this->assertSame($expectedResponse, $response);
    }

    public function testVerifyMfaCheckReturnsForbiddenOnVerificationFailure()
    {
        /** @var ChangePasswordHandler&PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(ChangePasswordHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'completeVerificationRequest'])
            ->getMock();

        /** @var LoggerInterface&PHPUnit_Framework_MockObject_MockObject $logger */
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('debug');
        $handler->setLogger($logger);

        $store = $this->createMock(StoreInterface::class);

        $handler->expects($this->once())->method('getStore')->willReturn($store);
        $handler->expects($this->once())->method('completeVerificationRequest')->willThrowException(
            new InvalidMethodException('foo')
        );

        $response = $handler->verifyMFACheck(new HTTPRequest('GET', ''));
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testVerifyMfaCheckReturnsUnsuccessfulValidationResult()
    {
        /** @var ChangePasswordHandler&PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(ChangePasswordHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'completeVerificationRequest'])
            ->getMock();

        $store = $this->createMock(StoreInterface::class);
        $handler->expects($this->once())->method('getStore')->willReturn($store);

        $handler->expects($this->once())->method('completeVerificationRequest')->willReturn(
            new Result(false, 'It is a test')
        );

        $response = $handler->verifyMFACheck(new HTTPRequest('GET', ''));
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertContains('It is a test', $response->getBody());
    }

    public function testVerifyMfaReturnsWhenVerificationIsNotComplete()
    {
        /** @var ChangePasswordHandler&PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(ChangePasswordHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'completeVerificationRequest', 'isVerificationComplete'])
            ->getMock();

        $store = $this->createMock(StoreInterface::class);

        $handler->expects($this->once())->method('getStore')->willReturn($store);
        $handler->expects($this->once())->method('completeVerificationRequest')->willReturn(new Result());
        $handler->expects($this->once())->method('isVerificationComplete')->willReturn(false);

        $response = $handler->verifyMFACheck(new HTTPRequest('GET', ''));
        $this->assertEquals(202, $response->getStatusCode());
        $this->assertContains('Additional authentication required', $response->getBody());
    }

    public function testVerifyMfaResultSuccessful()
    {
        /** @var ChangePasswordHandler&PHPUnit_Framework_MockObject_MockObject $handler */
        $handler = $this->getMockBuilder(ChangePasswordHandler::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStore', 'completeVerificationRequest', 'isVerificationComplete'])
            ->getMock();

        $store = new SessionStore($this->createMock(Member::class));

        $handler->expects($this->once())->method('getStore')->willReturn($store);
        $handler->expects($this->once())->method('completeVerificationRequest')->willReturn(new Result());
        $handler->expects($this->once())->method('isVerificationComplete')->willReturn(true);

        $request = new HTTPRequest('GET', '/');
        $request->setSession(new Session([]));
        $response = $handler->verifyMFACheck($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('Multi-factor authenticated', $response->getBody());
    }
}
