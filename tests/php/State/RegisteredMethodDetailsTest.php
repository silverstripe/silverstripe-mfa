<?php

namespace SilverStripe\MFA\Tests\State;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\State\RegisteredMethodDetails;

class RegisteredMethodDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MethodInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $method;

    /**
     * @var RegisteredMethodDetails
     */
    protected $details;

    protected function setUp()
    {
        parent::setUp();

        $this->method = $this->createMock(MethodInterface::class);
        $this->method->method('getLoginHandler')->willReturn(
            $this->createMock(LoginHandlerInterface::class)
        );

        $this->details = new RegisteredMethodDetails($this->method);
    }

    public function testJsonSerialize()
    {
        $this->method->expects($this->once())->method('getURLSegment')->willReturn('foo-bar');
        $result = json_encode($this->details);
        $this->assertContains('foo-bar', $result);
    }
}
