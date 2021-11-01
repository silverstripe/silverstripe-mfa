<?php

namespace SilverStripe\MFA\Tests\State;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\State\RegisteredMethodDetails;

class RegisteredMethodDetailsTest extends TestCase
{
    /**
     * @var MethodInterface|MockObject
     */
    protected $method;

    /**
     * @var RegisteredMethodDetails
     */
    protected $details;

    protected function setUp(): void
    {
        parent::setUp();

        $this->method = $this->createMock(MethodInterface::class);
        $this->method->method('getVerifyHandler')->willReturn(
            $this->createMock(VerifyHandlerInterface::class)
        );

        $this->details = new RegisteredMethodDetails($this->method);
    }

    public function testJsonSerialize()
    {
        $this->method->expects($this->once())->method('getURLSegment')->willReturn('foo-bar');
        $result = json_encode($this->details);
        $this->assertStringContainsString('foo-bar', $result);
    }
}
