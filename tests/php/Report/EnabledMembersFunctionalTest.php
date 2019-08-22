<?php

namespace SilverStripe\MFA\Tests\Report;

use SilverStripe\Dev\FunctionalTest;
use SilverStripe\MFA\Report\EnabledMembers;

class EnabledMembersFunctionalTest extends FunctionalTest
{
    protected static $fixture_file = 'EnabledMembersTest.yml';

    protected function setUp()
    {
        parent::setUp();

        $this->logInWithPermission();
    }

    public function testReportHasMemberNames()
    {
        $result = (string) $this->get(EnabledMembers::create()->getLink())->getBody();

        $this->assertContains('Eleanor', $result);
        $this->assertContains('Ditor', $result);
        $this->assertContains('Michelle', $result);
        $this->assertContains('Fa', $result);
        $this->assertContains('Ursula', $result);
        $this->assertContains('Ser', $result);
    }

    public function testReportHasRegisteredMethods()
    {
        $this->markTestSkipped('Temperamental test - consider revising');

        $result = (string) $this->get(EnabledMembers::create()->getLink())->getBody();

        $this->assertContains('Math problem, Null', $result);
    }

    public function testFilterReportByMemberName()
    {
        $this->get(EnabledMembers::create()->getLink());
        $response = $this->submitForm('Form_EditForm', 'action_updatereport', [
            'filters[Member]' => 'Michelle',
        ]);
        $result = (string) $response->getBody();

        $this->assertContains('mfa@example.com', $result);
        $this->assertNotContains('admin@example.com', $result);
    }

    public function testFilterReportBySkippedRegistration()
    {
        $this->get(EnabledMembers::create()->getLink());
        $response = $this->submitForm('Form_EditForm', 'action_updatereport', [
            'filters[Skipped]' => 'yes',
        ]);
        $result = (string) $response->getBody();

        $this->assertContains('user@example.com', $result);
        $this->assertNotContains('admin@example.com', $result);
    }
}
