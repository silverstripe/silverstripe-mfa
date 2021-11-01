<?php

namespace SilverStripe\MFA\Tests\BasicMath;

use PHPUnit\Framework\MockObject\MockObject;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Tests\Stub\Store\TestStore;
use SilverStripe\MFA\Tests\Stub\BasicMath\MethodVerifyHandler;

class MethodVerifyHandlerTest extends SapphireTest
{
    public function testStart()
    {
        $handler = new MethodVerifyHandler();

        /** @var TestStore|MockObject $store */
        $store = $this->createMock(TestStore::class);

        // Need to specify willReturn() otherwise mock will attempt to an interface which causes a fatal error in PHP8
        $store->expects($this->once())->method('setState')->willReturn($store);

        $mockRegisteredMethod = RegisteredMethod::create();

        $this->assertArrayHasKey('numbers', $handler->start($store, $mockRegisteredMethod));
    }

    public function testVerify()
    {
        $handler = new MethodVerifyHandler();

        /** @var HTTPRequest|MockObject $request */
        $request = $this->createMock(HTTPRequest::class);
        $request->expects($this->once())->method('getBody')->willReturn('{"answer":"10"}');

        /** @var TestStore|MockObject $store */
        $store = $this->createMock(TestStore::class);
        $store->expects($this->once())->method('getState')->willReturn([
            'answer' => 10,
        ]);

        $mockRegisteredMethod = RegisteredMethod::create();

        $this->assertTrue($handler->verify($request, $store, $mockRegisteredMethod)->isSuccessful());
    }
}
