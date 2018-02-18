<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Generators\CodeGenerator;
use SilverStripe\Dev\SapphireTest;

class CodeGeneratorTest extends SapphireTest
{

    public function testInst()
    {
        $this->assertInstanceOf(CodeGenerator::class, CodeGenerator::inst());
    }

    public function testChars()
    {
        /** @var CodeGenerator $generator */
        $generator = CodeGenerator::inst();

        $generator->setChars('1234567890');

        $code = $generator->generate();

        $this->assertTrue(is_numeric($code));

        $code = $generator->__toString();

        $this->assertTrue(is_numeric($code));
        $this->assertEquals(6, strlen($code));
    }
}