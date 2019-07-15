<?php

namespace SilverStripe\MFA\Tests\State;

use PHPUnit_Framework_MockObject_MockObject;
use SapphireTest;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\State\AvailableMethodDetails;

class AvailableMethodDetailsTest extends SapphireTest
{
    /**
     * @var MethodInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $method;

    /**
     * @var AvailableMethodDetails
     */
    protected $details;

    public function setUp()
    {
        parent::setUp();

        $this->method = $this->createMock(MethodInterface::class);
        $this->method->method('getRegisterHandler')->willReturn(
            $this->createMock(RegisterHandlerInterface::class)
        );

        $this->details = new AvailableMethodDetails($this->method);
    }

    public function testJsonSerialize()
    {
        $this->method->getRegisterHandler()->expects($this->once())->method('getName')->willReturn('Backup Codes');
        $result = json_encode($this->details);
        $this->assertContains('Backup Codes', $result);
    }
}
