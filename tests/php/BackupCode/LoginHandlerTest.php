<?php

namespace SilverStripe\MFA\Tests\BackupCode;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\BackupCode\LoginHandler;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;

class LoginHandlerTest extends SapphireTest
{
    protected static $fixture_file = 'LoginHandlerTest.yml';

    public function testVerifyReturnsFalseWithInvalidCode()
    {
        $handler = new LoginHandler();

        // Test a code with invalid characters
        list ($request, $store, $method) = $this->scaffoldVerifyParams('asw123');
        $this->assertFalse($handler->verify($request, $store, $method), 'Invalid characters are handled');

        // Test an empty code
        list ($request, $store, $method) = $this->scaffoldVerifyParams('');
        $this->assertFalse($handler->verify($request, $store, $method), 'Empty codes are handled');

        // Test a code param is not provided (null value)
        list ($request, $store, $method) = $this->scaffoldVerifyParams(null);
        $this->assertFalse($handler->verify($request, $store, $method), 'Null input is handled');

        // Test a code that's way too long
        list ($request, $store, $method) = $this->scaffoldVerifyParams(str_pad('', 10000, 'code'));
        $this->assertFalse($handler->verify($request, $store, $method), 'Long codes are handled');
    }

    public function testVerifyReturnsTrueOnValidCode()
    {
        $handler = new LoginHandler();

        // Test a code with invalid characters
        list ($request, $store, $method) = $this->scaffoldVerifyParams('123456');
        $this->assertTrue($handler->verify($request, $store, $method));
    }

    public function testVerifyInvalidatesCodesThatHaveBeenUsed()
    {
        $handler = new LoginHandler();

        // Test a code with invalid characters
        list ($request, $store, $method) = $this->scaffoldVerifyParams('123456');
        $this->assertTrue($handler->verify($request, $store, $method));

        $method = DataObject::get_by_id(RegisteredMethod::class, $method->ID);
        $codes = json_decode($method->Data, true);

        $this->assertCount(3, $codes, 'Only 3 codes remain against the method');

        list ($request, $store, $method) = $this->scaffoldVerifyParams('123456');
        $this->assertFalse(
            $handler->verify($request, $store, $method),
            'Attempting to validate the previously used code now returns false'
        );
    }

    protected function scaffoldVerifyParams($userInput)
    {
        /** @var Member|MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');

        /** @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HTTPRequest::class);
        $request->expects($this->once())->method('param')->with('code')->willReturn($userInput);

        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->any())->method('getMember')->willReturn($member);

        /** @var RegisteredMethod $registeredMethod */
        $registeredMethod = $member->RegisteredMFAMethods()->first();

        return [$request, $store, $registeredMethod];
    }
}
