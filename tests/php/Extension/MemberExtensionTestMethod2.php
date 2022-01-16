<?php

namespace SilverStripe\MFA\Tests\Extension;

use SilverStripe\MFA\BackupCode\Method;

/**
 * This needs to be a separate class rather than an anonymous class
 * because Injector in php 7.3 doesn't work with an anonmyous classes
 * in this context
 */
class MemberExtensionTestMethod2 extends Method
{
    public function getName(): string
    {
        return 'MemberExtensionTestMethod2';
    }
    public function getURLSegment(): string
    {
        return 'MemberExtensionTestMethod2';
    }
}
