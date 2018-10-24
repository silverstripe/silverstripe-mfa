<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Generators\CodeGenerator;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;

class CodeGeneratorTest extends SapphireTest
{
    public function testInst()
    {
        $this->assertInstanceOf(CodeGenerator::class, CodeGenerator::inst());
    }

    public function testGlobal()
    {
        Config::modify()->set(CodeGenerator::class, 'length', 10);

        /** @var CodeGenerator $generator */
        $generator = CodeGenerator::global_inst();

        $this->assertInstanceOf(CodeGenerator::class, $generator);

        // Default length set by the generator, despite the above 10
        $this->assertEquals(6, $generator->getLength());
    }

    public function testChars()
    {
        /** @var CodeGenerator $generator */
        $generator = CodeGenerator::inst();

        $generator->setChars('1234567890');

        $generator->setLength(6);

        $code = $generator->generate();

        $this->assertTrue(is_numeric($code));

        $code = $generator->__toString();

        $this->assertTrue(is_numeric($code));
        $this->assertEquals(6, strlen($code));
    }
}
