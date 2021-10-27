<?php

namespace SilverStripe\MFA\Tests\Extension\AccountReset;

use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Extension\AccountReset\SecurityAdminExtension;
use SilverStripe\Security\Member;
use SilverStripe\Security\SecurityToken;

/**
 * Class SecurityAdminExtensionTest
 *
 * @see SecurityAdminExtension
 */
class SecurityAdminExtensionTest extends FunctionalTest
{
    protected static $fixture_file = 'SecurityAdminExtensionTest.yml';

    protected function setUp(): void
    {
        parent::setUp();

        SecurityToken::enable();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        SecurityToken::disable();
    }

    public function testEndpointRequiresCSRF()
    {
        $this->logInAs('admin');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'squib');

        $response = $this->post(SecurityAdmin::singleton()->Link("reset/{$member->ID}"), [true]);

        $this->assertEquals(400, $response->getStatusCode(), $response->getBody());
        $this->assertStringContainsString('Invalid or missing CSRF', $response->getBody());
    }

    public function testResetCanBeInitiatedByAdmin()
    {
        $this->logInAs('admin');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'squib');

        $response = $this->post(
            SecurityAdmin::singleton()->Link("reset/{$member->ID}"),
            [true],
            null,
            null,
            json_encode(['csrf_token' => SecurityToken::inst()->getValue()])
        );

        $this->assertEquals(200, $response->getStatusCode(), $response->getBody());
        $this->assertEmailSent($member->Email);
    }

    public function testResetCannotBeInitiatedByStandardUser()
    {
        $this->logInAs('squib');

        /** @var Member $member */
        $member = $this->objFromFixture(Member::class, 'admin');

        $response = $this->post(
            SecurityAdmin::singleton()->Link("reset/{$member->ID}"),
            [true],
            null,
            null,
            json_encode(['csrf_token' => SecurityToken::inst()->getValue()])
        );

        $this->assertEquals(403, $response->getStatusCode(), $response->getBody());
        $this->assertStringContainsString('Insufficient permissions', $response->getBody());
    }
}
