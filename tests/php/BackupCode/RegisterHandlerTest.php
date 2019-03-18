<?php

namespace SilverStripe\MFA\Tests\BackupCode;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\BackupCode\Method;
use SilverStripe\MFA\BackupCode\RegisterHandler;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class RegisterHandlerTest extends SapphireTest
{
    protected static $fixture_file = 'LoginHandlerTest.yml';

    public function testStartReturnsPlainTextCodes()
    {
        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->exactly(2))->method('getMember')->willReturn(Security::getCurrentUser());

        $handler = new RegisterHandler();

        $props = $handler->start($store);

        $this->assertArrayHasKey('codes', $props, 'Codes are returned from the start method');
        $this->assertGreaterThan(0, count($props['codes']), 'At least one code is provided');
        $this->assertTrue(is_numeric(reset($props['codes'])), 'Codes produced are numeric');
    }

    public function testStartGeneratesCodesMatchingConfig()
    {
        Config::modify()->set(Method::class, 'backup_code_count', 5);
        Config::modify()->set(Method::class, 'backup_code_length', 12); // Exceeds PHP_INT_MAX on 32 bit

        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->exactly(2))->method('getMember')->willReturn(Security::getCurrentUser());

        $handler = new RegisterHandler();

        $props = $handler->start($store);

        $this->assertArrayHasKey('codes', $props, 'Codes are returned from the start method');

        $codes = $props['codes'];

        $this->assertCount(5, $codes, 'Only 5 codes are generated as configured');

        foreach ($codes as $code) {
            $this->assertSame(12, strlen($code), 'Codes are 12 characters long as configured');
            $this->assertTrue(is_numeric($code), 'Codes generated are numeric');
        }
    }

    public function testStartStoresHashesOfBackupCodesOnMember()
    {
        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);

        /** @var Member|MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');
        $store->expects($this->exactly(2))->method('getMember')->willReturn($member);

        $handler = new RegisterHandler();

        // Kill polluted test state :(
        foreach ($member->RegisteredMFAMethods() as $method) {
            $method->delete();
        }

        // Ensure the "current" user has no existing backup codes
        $registeredMethod = MethodRegistry::singleton()->getRegisteredMethodFromMember($member, 'backup-codes');
        $this->assertNull($registeredMethod, 'No backup codes are stored yet');

        // Generate the codes and assert and store what's given back to the UI
        $props = $handler->start($store);
        $this->assertArrayHasKey('codes', $props, 'Codes are returned from the start method');
        $codes = $props['codes'];

        // Check the registered methods on the member as the start method should have saved new backup codes
        $registeredMethod = MethodRegistry::singleton()->getRegisteredMethodFromMember($member, 'backup-codes');
        $this->assertTrue($registeredMethod->isInDB(), 'Backup codes are stored');
        $this->assertJson($registeredMethod->Data, 'Backup codes are stored as valid JSON');

        // Parse the stored codes on the member to compare with what was given to the UI
        $storedCodes = json_decode($registeredMethod->Data, true);

        $this->assertCount(count($codes), $storedCodes, 'Stored code count matches that of those returned to the UI');

        foreach ($codes as $code) {
            $this->assertNotContains($code, $storedCodes, 'Codes returned to UI are plaintext (different from stored)');
        }
    }

    public function testRegisterReturnsNothing()
    {
        $store = $this->createMock(StoreInterface::class);

        /** @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HTTPRequest::class);

        $handler = new RegisterHandler();
        $result = $handler->register($request, $store);

        $this->assertEmpty($result);
    }
}
