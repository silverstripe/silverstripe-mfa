<?php

namespace SilverStripe\MFA\Tests\Controller;

use SilverStripe\Admin\AdminRootController;
use SilverStripe\Control\Controller;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Store\SessionStore;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\Security\Member;

class AdminRegistrationControllerTest extends FunctionalTest
{
    protected static $fixture_file = 'AdminRegistrationControllerTest.yml';

    protected function setUp()
    {
        parent::setUp();

        MethodRegistry::config()->set('methods', [
            BasicMathMethod::class,
        ]);
    }

    public function testStartRegistrationAssertsValidMethod()
    {
        $this->logInAs($this->objFromFixture(Member::class, 'sally_smith'));

        $result = $this->get(Controller::join_links(AdminRootController::admin_url(), 'mfa', 'register/foo'));

        $this->assertSame(400, $result->getStatusCode());
        $this->assertContains('No such method is available', $result->getBody());
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
        $this->assertContains('Invalid session', $result->getBody());
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
        $this->assertContains('No such method is available', $result->getBody());
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
}
