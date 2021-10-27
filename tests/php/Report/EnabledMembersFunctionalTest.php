<?php

namespace SilverStripe\MFA\Tests\Report;

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Report\EnabledMembers;

class EnabledMembersFunctionalTest extends FunctionalTest
{
    protected static $fixture_file = 'EnabledMembersTest.yml';

    protected function setUp(): void
    {
        parent::setUp();

        $this->logInWithPermission();
    }

    public function testReportHasMemberNames()
    {
        $result = (string) $this->get(EnabledMembers::create()->getLink())->getBody();

        $this->assertStringContainsString('Eleanor', $result);
        $this->assertStringContainsString('Ditor', $result);
        $this->assertStringContainsString('Michelle', $result);
        $this->assertStringContainsString('Fa', $result);
        $this->assertStringContainsString('Ursula', $result);
        $this->assertStringContainsString('Ser', $result);
    }

    public function testReportHasRegisteredMethods()
    {
        $this->markTestSkipped('Temperamental test - consider revising');

        $result = (string) $this->get(EnabledMembers::create()->getLink())->getBody();

        $this->assertStringContainsString('Math problem, Null', $result);
    }

    public function testFilterReportByMemberName()
    {
        $this->get(EnabledMembers::create()->getLink());
        $response = $this->submitForm('Form_EditForm', 'action_updatereport', [
            'filters[Member]' => 'Michelle',
        ]);
        $result = (string) $response->getBody();

        $this->assertStringContainsString('mfa@example.com', $result);
        $this->assertStringNotContainsString('admin@example.com', $result);
    }

    public function testFilterReportBySkippedRegistration()
    {
        $this->get(EnabledMembers::create()->getLink());
        $response = $this->submitForm('Form_EditForm', 'action_updatereport', [
            'filters[Skipped]' => 'yes',
        ]);
        $result = (string) $response->getBody();

        $this->assertStringContainsString('user@example.com', $result);
        $this->assertStringNotContainsString('admin@example.com', $result);
    }
}
