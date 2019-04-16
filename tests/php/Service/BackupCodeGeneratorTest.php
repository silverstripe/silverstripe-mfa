<?php declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\Tests\Service\BackupCodeGeneratorTest\MockHashExtension;

class BackupCodeGeneratorTest extends SapphireTest
{
    protected static $required_extensions = [
        BackupCodeGenerator::class => [
            MockHashExtension::class,
        ],
    ];

    protected function setUp()
    {
        parent::setUp();

        BackupCodeGenerator::config()
            ->set('backup_code_count', 3)
            ->set('backup_code_length', 6);
    }

    public function testHash()
    {
        $generator = new BackupCodeGenerator();
        $result = $generator->hash('hello world');
        $this->assertSame('dlrow olleh', $result);
    }

    public function testGenerate()
    {
        $generator = new BackupCodeGenerator();
        $result = $generator->generate();

        $this->assertCount(3, $result, 'Expected number of codes are generated');
        foreach ($result as $code => $hash) {
            $this->assertSame(6, strlen($code), 'Generated codes are of configured length');
            $this->assertSame(strrev($code), $hash, 'Mock hashing method is used and hash is returned');
        }
    }
}
