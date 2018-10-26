<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Forms\BootstrapMFALoginForm;
use Firesphere\BootstrapMFA\Handlers\BootstrapMFALoginHandler;
use Firesphere\BootstrapMFA\Tests\Mocks\MockAuthenticator;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\Debug;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FormField;
use SilverStripe\Security\Member;

class BootstrapMFALoginHandlerTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/member.yml';

    /**
     * @var HTTPRequest
     */
    protected $request;

    /**
     * @var Member
     */
    protected $member;

    /**
     * @var BootstrapMFAAuthenticator
     */
    protected $authenticator;

    /**
     * @var BootstrapMFALoginForm
     */
    protected $form;

    /**
     * @var BootstrapMFALoginHandler
     */
    protected $handler;

    public function testLoginForm()
    {
        $form = $this->handler->LoginForm();

        $this->assertInstanceOf(BootstrapMFALoginForm::class, $form);
    }

    public function testDoLogin()
    {
        $data = [
            'Email'    => 'test@test.com',
            'Password' => 'password1'
        ];

        $request = Controller::curr()->getRequest();

        $session = new Session(['key' => 'value']);
        $session->init($request);

        $request->setSession($session);

        $response = $this->handler->doLogin($data, $this->form, $request);

        $this->assertInstanceOf(HTTPResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('verify', $response->getHeader('location'));

        $session = $request->getSession();
        $expected = [
            'MemberID' => 1,
            'Data'     =>
                [
                    'Email'    => 'test@test.com',
                    'Password' => 'password1',
                ]
        ];
        $this->assertEquals($expected, $session->get(BootstrapMFAAuthenticator::SESSION_KEY));

        $session = new Session(['key' => 'value']);
        $session->init($request);

        $request->setSession($session);

        $data = [
            'Email'    => 'admin',
            'Password' => 'password'
        ];

        $this->handler->setRequest($request);
        $response = $this->handler->doLogin($data, $this->form, $request);

        $this->assertEquals(302, $response->getStatusCode());
        // We don't have a backURL, so expect to go back to '/login'
        $this->assertContains('login', $response->getHeader('location'));
    }

    public function testBackURLLogin()
    {
        $data = [
            'Email'    => 'test@test.com',
            'Password' => 'password1',
            'BackURL'  => '/memberlocation'
        ];

        $request = Controller::curr()->getRequest();

        $session = new Session(['key' => 'value']);
        $session->init($request);

        $request->setSession($session);

        $response = $this->handler->doLogin($data, $this->form, $request);

        $this->assertInstanceOf(HTTPResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('verify', $response->getHeader('location'));

        $session = $request->getSession();
        $expected = [
            'MemberID' => 1,
            'BackURL'  => '/memberlocation',
            'Data'     =>
                [
                    'Email'    => 'test@test.com',
                    'Password' => 'password1',
                    'BackURL'  => '/memberlocation',
                ],
        ];
        $this->assertEquals($expected, $session->get(BootstrapMFAAuthenticator::SESSION_KEY));
    }

    public function testDoWrongLogin()
    {
        $data = [
            'Email'    => 'test@test.com',
            'Password' => 'wrongpassword'
        ];

        $response = $this->handler->doLogin($data, $this->form, $this->request);

        $this->assertInstanceOf(HTTPResponse::class, $response);
        $this->assertEquals(302, $response->getStatusCode());
        $this->assertContains('login', $response->getHeader('location'));
    }

    public function testSecondFactor()
    {
        $result = $this->handler->secondFactor($this->request);

        $this->assertArrayHasKey('Form', $result);
        /** @var BootstrapMFALoginForm $form */
        $form = $result['Forms'][0];
        /** @var FormField $field */
        $field = $form->Fields()->dataFieldByName('AuthenticationMethod');
        $this->assertEquals(MockAuthenticator::class, $field->Value());
    }

    public function testValidateMFA()
    {
        $data = [
            'Email'    => 'test@test.com',
            'Password' => 'password1',
            'BackURL'  => '/memberlocation'
        ];

        $request = Controller::curr()->getRequest();

        $session = new Session(['key' => 'value']);
        $session->init($request);

        $request->setSession($session);

        $response = $this->handler->doLogin($data, $this->form, $request);

        $session = $request->getSession();
        $data = [
            'AuthenticationMethod' => MockAuthenticator::class,
            'token' => 'success'
        ];

        $request = new HTTPRequest('POST', 'Security/default/validateMFA', [], $data);
        $request->setSession($session);

        $this->handler->setRequest($request);

        $verification = $this->handler->validateMFA($request);

        $this->assertEquals(302, $verification->getStatusCode());
        $this->assertContains('memberlocation', $verification->getHeader('location'));

        $data = [
            'AuthenticationMethod' => MockAuthenticator::class,
            'token' => 'error'
        ];

        $request = new HTTPRequest('POST', 'Security/default/validateMFA', [], $data);
        $request->setSession($session);

        $this->handler->setRequest($request);

        $verification = $this->handler->validateMFA($request);

        $this->assertEquals(302, $verification->getStatusCode());
        $this->assertNotContains('memberlocation', $verification->getHeader('location'));
        $this->assertContains('login', $verification->getHeader('location'));
        $session = $request->getSession();
        $this->assertEmpty($session->get(BootstrapMFAAuthenticator::SESSION_KEY));

    }

    protected function setUp()
    {
        parent::setUp();
        $this->request = Controller::curr()->getRequest();
        $this->member = $this->objFromFixture(Member::class, 'member1');

        $session = new Session([]);
        $session->set(BootstrapMFAAuthenticator::SESSION_KEY . '.MemberID', $this->member->ID);
        $this->request->setSession($session);

        $this->authenticator = Injector::inst()->create(BootstrapMFAAuthenticator::class);
        $this->form = Injector::inst()->createWithArgs(
            BootstrapMFALoginForm::class,
            [Controller::curr(), $this->authenticator, 'test']
        );
        $this->handler = Injector::inst()->createWithArgs(
            BootstrapMFALoginHandler::class,
            ['login', $this->authenticator]
        );
    }
}
