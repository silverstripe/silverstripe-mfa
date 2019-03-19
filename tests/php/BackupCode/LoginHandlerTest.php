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

    /**
     * @dataProvider getVerifyTests
     */
    public function testVerifyValidatesCodes($expectedResult, $input, $message)
    {
        $handler = new LoginHandler();

        // Test a code with invalid characters
        list ($request, $store, $method) = $this->scaffoldVerifyParams($input);
        $this->assertSame($expectedResult, $handler->verify($request, $store, $method), $message);
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

    public function getVerifyTests()
    {
        return [
            [false, 'asw123', 'Invalid characters are handled'],
            [false, '', 'Empty codes are handled'],
            [false, null, 'Null input is handled'],
            [false, str_pad('', 10000, 'code'), 'Long codes are handled'],
            [true, '123456', 'Valid codes are valid'],
        ];
    }

    protected function scaffoldVerifyParams($userInput)
    {
        /** @var Member|MemberExtension $member */
        $member = $this->objFromFixture(Member::class, 'guy');

        /** @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HTTPRequest::class);
        $request->expects($this->once())->method('getBody')->willReturn("{\"code\":\"{$userInput}\"}");

        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->any())->method('getMember')->willReturn($member);

        /** @var RegisteredMethod $registeredMethod */
        $registeredMethod = $member->RegisteredMFAMethods()->first();

        return [$request, $store, $registeredMethod];
    }
}
