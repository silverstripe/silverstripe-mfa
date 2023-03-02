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
        $result = (string) $this->get(EnabledMembers::create()->getLink())->getBody();
        $this->assertStringContainsString('Math problem, Null', $result);
    }

    public function testFilterReportByMemberName()
    {
        $result = $this->get('/admin/reports/show/SilverStripe-MFA-Report-EnabledMembers?filters%5BMember%5D=Michelle');
        $this->assertStringContainsString('mfa@example.com', $result);
        $this->assertStringNotContainsString('admin@example.com', $result);
    }

    public function testFilterReportBySkippedRegistration()
    {
        $result = $this->get('/admin/reports/show/SilverStripe-MFA-Report-EnabledMembers?filters%5BSkipped%5D=yes');
        $this->assertStringContainsString('user@example.com', $result);
        $this->assertStringNotContainsString('admin@example.com', $result);
    }
}
