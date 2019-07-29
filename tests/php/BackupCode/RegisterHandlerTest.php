<?php

namespace SilverStripe\MFA\Tests\BackupCode;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\BackupCode\Method;
use SilverStripe\MFA\BackupCode\RegisterHandler;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class RegisterHandlerTest extends SapphireTest
{
    protected static $fixture_file = 'RegisterHandlerTest.yml';

    /**
     * @expectedException \SilverStripe\MFA\Exception\RegistrationFailedException
     * @expectedExceptionMessage Attempted to register backup codes with no registered methods
     */
    public function testStartThrowsExceptionForMemberWithoutRegisteredMethods()
    {
        /** @var StoreInterface&PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');
        $member->RegisteredMFAMethods()->removeAll();
        $store->expects($this->once())->method('getMember')->willReturn($member);

        $handler = new RegisterHandler();
        $handler->start($store);
    }

    public function testStartReturnsPlainTextCodes()
    {
        /** @var Member&MemberExtension $member */
        $member = Security::getCurrentUser();
        $method = new BasicMathMethod();
        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['need' => 'one']);

        /** @var StoreInterface&PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getMember')->willReturn($member);

        $handler = new RegisterHandler();

        $props = $handler->start($store);

        $this->assertArrayHasKey('codes', $props, 'Codes are returned from the start method');
        $this->assertGreaterThan(0, count($props['codes']), 'At least one code is provided');
    }

    public function testStartStoresHashesOfBackupCodesOnMember()
    {
        /** @var StoreInterface&PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);

        /** @var Member&MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');
        $method = new BasicMathMethod();
        RegisteredMethodManager::singleton()->registerForMember($member, $method, ['need' => 'one']);
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
        /** @var StoreInterface&PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);

        /** @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HTTPRequest::class);

        $handler = new RegisterHandler();
        $result = $handler->register($request, $store);

        $this->assertTrue($result->isSuccessful());
        $this->assertEmpty($result->getContext());
    }
}
