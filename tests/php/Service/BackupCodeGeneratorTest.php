<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Service;

use Config;
use SapphireTest;
use SilverStripe\MFA\State\BackupCode;

class BackupCodeGeneratorTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();

        Config::inst()->remove(BackupCodeGenerator::class, 'backup_code_count');
        Config::inst()->update(BackupCodeGenerator::class, 'backup_code_count', 3);
        Config::inst()->remove(BackupCodeGenerator::class, 'backup_code_length');
        Config::inst()->update(BackupCodeGenerator::class, 'backup_code_length', 6);
    }

    public function testGenerate()
    {
        $generator = new BackupCodeGenerator();
        /** @var BackupCode[] $result */
        $result = $generator->generate();

        $this->assertCount(3, $result, 'Expected number of codes are generated');
        foreach ($result as $backupCode) {
            $this->assertSame(
                6,
                strlen($backupCode->getCode()),
                'Generated codes are of configured length'
            );
        }
    }
}
