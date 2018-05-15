<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Handlers\BootstrapMFAChangePasswordHandler;
use Firesphere\BootstrapMFA\Tests\Helpers\CodeHelper;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Session;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Security\IdentityStore;
use SilverStripe\Security\Member;
use SilverStripe\Security\MemberAuthenticator\ChangePasswordForm;

class BootstrapMFAChangePasswordHandlerTest extends SapphireTest
{
    protected static $fixture_file = '../fixtures/member.yml';

    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var BootstrapMFAAuthenticator
     */
    protected $authenticator;

    /**
     * @var BootstrapMFAChangePasswordHandler
     */
    protected $handler;

    /**
     * @var ChangePasswordForm
     */
    protected $form;

    public function testDoChangePassword()
    {
        $user = $this->objFromFixture(Member::class, 'member1');
        Injector::inst()->get(IdentityStore::class)->logIn($user);

        $data = [
            'OldPassword'  => 'password1',
            'NewPassword1' => 'password',
            'NewPassword2' => 'password',
        ];

        $response = $this->handler->doChangePassword($data, $this->form);

        $this->assertInstanceOf(HTTPResponse::class, $response);
        $codes = CodeHelper::getCodesFromSession();
        $this->assertEquals(15, count($codes));
    }

    protected function setUp()
    {
        parent::setUp();
        $this->controller = Controller::curr();
        $request = $this->controller->getRequest();
        $request->setSession(new Session(['test' => 'test']));
        $this->authenticator = Injector::inst()->create(BootstrapMFAAuthenticator::class);
        $this->form = Injector::inst()->createWithArgs(
            ChangePasswordForm::class,
            [$this->controller, 'changepassword']
        );

        $this->handler = Injector::inst()->createWithArgs(
            BootstrapMFAChangePasswordHandler::class,
            ['changepassword', $this->authenticator]
        );
        $this->handler->setRequest($request);
    }
}
