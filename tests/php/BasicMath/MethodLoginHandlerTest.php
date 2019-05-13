<?php

namespace SilverStripe\MFA\Tests\BasicMath;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\MFA\Tests\Stub\BasicMath\MethodVerifyHandler;

class MethodLoginHandlerTest extends SapphireTest
{
    public function testStart()
    {
        $handler = new MethodVerifyHandler();

        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('setState');

        $mockRegisteredMethod = RegisteredMethod::create();

        $this->assertArrayHasKey('numbers', $handler->start($store, $mockRegisteredMethod));
    }

    public function testVerify()
    {
        $handler = new MethodVerifyHandler();

        /** @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HTTPRequest::class);
        $request->expects($this->once())->method('getBody')->willReturn('{"answer":"10"}');

        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getState')->willReturn([
            'answer' => 10,
        ]);

        $mockRegisteredMethod = RegisteredMethod::create();

        $this->assertTrue($handler->verify($request, $store, $mockRegisteredMethod)->isSuccessful());
    }
}
