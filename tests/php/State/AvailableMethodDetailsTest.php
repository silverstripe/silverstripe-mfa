<?php

namespace SilverStripe\MFA\Tests\State;

use PHPUnit_Framework_MockObject_MockObject;
use SilverStripe\Dev\SapphireTest;
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

    protected function setUp()
    {
        parent::setUp();

        $this->method = $this->createMock(MethodInterface::class);
        $this->method->method('getRegisterHandler')->willReturn(
            $this->createMock(RegisterHandlerInterface::class)
        );

        $this->details = new AvailableMethodDetails($this->method);
    }

    public function testGetDescription()
    {
        $this->method->getRegisterHandler()->expects($this->once())->method('getDescription')->willReturn('foo');
        $this->assertSame('foo', $this->details->getDescription());
    }

    public function testGetName()
    {
        $this->method->getRegisterHandler()->expects($this->once())->method('getName')->willReturn('Backup Codes');
        $this->assertSame('Backup Codes', $this->details->getName());
    }

    public function testJsonSerialize()
    {
        $this->method->getRegisterHandler()->expects($this->once())->method('getName')->willReturn('Backup Codes');
        $result = json_encode($this->details);
        $this->assertContains('Backup Codes', $result);
    }

    public function testGetSupportLink()
    {
        $this->method->getRegisterHandler()->expects($this->once())->method('getSupportLink')->willReturn('google.com');
        $this->assertSame('google.com', $this->details->getSupportLink());
    }

    public function testGetURLSegment()
    {
        $this->method->expects($this->once())->method('getURLSegment')->willReturn('backup-codes');
        $this->assertSame('backup-codes', $this->details->getURLSegment());
    }

    public function testGetThumbnail()
    {
        $this->method->expects($this->once())->method('getThumbnail')->willReturn('clipper');
        $this->assertSame('clipper', $this->details->getThumbnail());
    }

    public function testIsAvailable()
    {
        $this->method->getRegisterHandler()
            ->expects($this->exactly(2))
            ->method('isAvailable')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertFalse($this->details->isAvailable());
        $this->assertTrue($this->details->isAvailable());
    }

    public function testGetUnavailableMesssage()
    {
        $this->method->getRegisterHandler()
            ->expects($this->once())
            ->method('getUnavailableMessage')
            ->willReturn('We don\'t like it.');
        $this->assertSame('We don\'t like it.', $this->details->getUnavailableMessage());
    }
}
