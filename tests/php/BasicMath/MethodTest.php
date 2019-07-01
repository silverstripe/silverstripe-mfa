<?php

namespace SilverStripe\MFA\Tests\BasicMath;

use SapphireTest;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Tests\Stub\BasicMath\Method;

class MethodTest extends SapphireTest
{
    public function testGetVerifyHandler()
    {
        $method = new Method();
        $this->assertInstanceOf(VerifyHandlerInterface::class, $method->getVerifyHandler());
    }

    public function testGetRegisterHandler()
    {
        $method = new Method();
        $this->assertInstanceOf(RegisterHandlerInterface::class, $method->getRegisterHandler());
    }
}
