<?php

namespace SilverStripe\MFA\Tests\BackupCode;

use PHPUnit\Framework\MockObject\MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\BackupCode\Method;
use SilverStripe\MFA\BackupCode\RegisterHandler;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Tests\Stub\Store\TestStore;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method as BasicMathMethod;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class RegisterHandlerTest extends SapphireTest
{
    protected static $fixture_file = 'RegisterHandlerTest.yml';

    public function testStartThrowsExceptionForMemberWithoutRegisteredMethods()
    {
        $this->expectException(\SilverStripe\MFA\Exception\RegistrationFailedException::class);
        $this->expectExceptionMessage('Attempted to register backup codes with no registered methods');
        /** @var TestStore&MockObject $store */
        $store = $this->createMock(TestStore::class);

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

        /** @var TestStore&MockObject $store */
        $store = $this->createMock(TestStore::class);
        $store->expects($this->once())->method('getMember')->willReturn($member);

        $handler = new RegisterHandler();

        $props = $handler->start($store);

        $this->assertArrayHasKey('codes', $props, 'Codes are returned from the start method');
        $this->assertGreaterThan(0, count($props['codes'] ?? []), 'At least one code is provided');
    }

    public function testStartStoresHashesOfBackupCodesOnMember()
    {
        /** @var TestStore&MockObject $store */
        $store = $this->createMock(TestStore::class);

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
        $storedCodes = json_decode($registeredMethod->Data ?? '', true);

        $this->assertCount(
            count($codes ?? []),
            $storedCodes,
            'Stored code count matches that of those returned to the UI'
        );

        foreach ($codes as $code) {
            $this->assertNotContains($code, $storedCodes, 'Codes returned to UI are plaintext (different from stored)');
        }
    }

    public function testRegisterReturnsNoContext()
    {
        /** @var TestStore&MockObject $store */
        $store = $this->createMock(TestStore::class);

        /** @var HTTPRequest|MockObject $request */
        $request = $this->createMock(HTTPRequest::class);

        $handler = new RegisterHandler();
        $result = $handler->register($request, $store);

        $this->assertTrue($result->isSuccessful());
        $this->assertEmpty($result->getContext());
    }
}
