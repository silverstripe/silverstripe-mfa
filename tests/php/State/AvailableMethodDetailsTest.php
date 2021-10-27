<?php

namespace SilverStripe\MFA\Tests\State;

use PHPUnit\Framework\MockObject\MockObject;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\State\AvailableMethodDetails;

class AvailableMethodDetailsTest extends SapphireTest
{
    /**
     * @var MethodInterface|MockObject
     */
    protected $method;

    /**
     * @var AvailableMethodDetails
     */
    protected $details;

    protected function setUp(): void
    {
        parent::setUp();

        $this->method = $this->createMock(MethodInterface::class);
        $this->details = new AvailableMethodDetails($this->method);
    }

    public function testJsonSerialize()
    {
        $this->method->expects($this->once())->method('getName')->willReturn('Backup Codes');
        $result = json_encode($this->details);
        $this->assertStringContainsString('Backup Codes', $result);
    }
}
