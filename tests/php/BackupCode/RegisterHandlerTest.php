<?php

namespace SilverStripe\MFA\Tests\BackupCode;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\BackupCode\Method;
use SilverStripe\MFA\BackupCode\RegisterHandler;
use SilverStripe\MFA\Store\StoreInterface;

class RegisterHandlerTest extends SapphireTest
{
    public function testStartReturnsPlainTextCodes()
    {
        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('setState');

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
        $store->expects($this->once())->method('setState');

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

    public function testStartStoresHashesOfBackupCodes()
    {
        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $storedCodes = [];
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('setState')->willReturnCallback(function ($codes) use (&$storedCodes) {
            $storedCodes = $codes;
            return true;
        });

        $handler = new RegisterHandler();

        $props = $handler->start($store);

        $this->assertArrayHasKey('codes', $props, 'Codes are returned from the start method');

        $codes = $props['codes'];

        $this->assertCount(count($codes), $storedCodes, 'Stored code count matches that of those returned by start');

        foreach ($codes as $code) {
            $this->assertNotContains($code, $storedCodes, 'Code return to UI is not stored in plaintext');
        }
    }

    public function testRegisterReturnsStoredState()
    {
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getState')->willReturn('something');

        /** @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HTTPRequest::class);

        $handler = new RegisterHandler();
        $result = $handler->register($request, $store);

        $this->assertSame('something', $result);
    }
}
