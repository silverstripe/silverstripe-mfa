<?php

namespace SilverStripe\MFA\Tests\BackupCode;

use PHPUnit_Framework_MockObject_MockObject;
use SS_HTTPRequest as HTTPRequest;
use Injector;
use SapphireTest;
use SilverStripe\MFA\BackupCode\Method;
use SilverStripe\MFA\BackupCode\RegisterHandler;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Store\StoreInterface;
use Member;
use Security;

class RegisterHandlerTest extends SapphireTest
{
    protected static $fixture_file = 'RegisterHandlerTest.yml';

    public function testStartReturnsPlainTextCodes()
    {
        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getMember')->willReturn(Security::getCurrentUser());

        $handler = new RegisterHandler();

        $props = $handler->start($store);

        $this->assertArrayHasKey('codes', $props, 'Codes are returned from the start method');
        $this->assertGreaterThan(0, count($props['codes']), 'At least one code is provided');
    }

    public function testStartStoresHashesOfBackupCodesOnMember()
    {
        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);

        /** @var Member|MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');
        $store->expects($this->once())->method('getMember')->willReturn($member);

        $handler = new RegisterHandler();

        // Ensure the "current" user has no existing backup codes
        $registeredMethod = RegisteredMethodManager::singleton()->getFromMember($member, new Method());
        $this->assertNull($registeredMethod, 'No backup codes are stored yet');

        // Generate the codes and assert and store what's given back to the UI
        $props = $handler->start($store);
        $this->assertArrayHasKey('codes', $props, 'Codes are returned from the start method');
        $codes = $props['codes'];

        // Check the registered methods on the member as the start method should have saved new backup codes
        $registeredMethod = RegisteredMethodManager::singleton()->getFromMember($member, new Method());
        $this->assertTrue($registeredMethod->isInDB(), 'Backup codes are stored');
        $this->assertJson($registeredMethod->Data, 'Backup codes are stored as valid JSON');

        // Parse the stored codes on the member to compare with what was given to the UI
        $storedCodes = json_decode($registeredMethod->Data, true);

        $this->assertCount(count($codes), $storedCodes, 'Stored code count matches that of those returned to the UI');

        foreach ($codes as $code) {
            $this->assertNotContains($code, $storedCodes, 'Codes returned to UI are plaintext (different from stored)');
        }
    }

    public function testRegisterReturnsNoContext()
    {
        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);

        /** @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HTTPRequest::class);

        $handler = new RegisterHandler();
        $result = $handler->register($request, $store);

        $this->assertTrue($result->isSuccessful());
        $this->assertEmpty($result->getContext());
    }
}
