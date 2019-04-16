<?php

namespace SilverStripe\MFA\Tests\Service\BackupCodeGeneratorTest;

use SilverStripe\Core\Extension;
use SilverStripe\Dev\TestOnly;

class MockHashExtension extends Extension implements TestOnly
{
    /**
     * Mock the hashing process by returning the reversed input
     *
     * @param string $code
     * @param string $hash
     */
    public function updateHash($code, &$hash)
    {
        $hash = strrev($code);
    }
}
