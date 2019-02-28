<?php

namespace SilverStripe\MFA\Tests\BasicMath;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\BasicMath\MethodLoginHandler;
use SilverStripe\MFA\Store\StoreInterface;

class MethodLoginHandlerTest extends SapphireTest
{
    public function testStart()
    {
        $handler = new MethodLoginHandler();

        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('setState');

        $this->assertArrayHasKey('numbers', $handler->start($store));
    }

    public function testVerify()
    {
        $handler = new MethodLoginHandler();

        /** @var HTTPRequest|PHPUnit_Framework_MockObject_MockObject $request */
        $request = $this->createMock(HTTPRequest::class);
        $request->expects($this->once())->method('param')->with('answer')->willReturn(10);

        /** @var StoreInterface|PHPUnit_Framework_MockObject_MockObject $store */
        $store = $this->createMock(StoreInterface::class);
        $store->expects($this->once())->method('getState')->willReturn([
            'answer' => 10,
        ]);

        $this->assertTrue($handler->verify($request, $store));
    }
}
