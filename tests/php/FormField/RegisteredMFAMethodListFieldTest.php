<?php

namespace SilverStripe\MFA\Tests\FormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\FormField\RegisteredMFAMethodListField;
use TypeError;

class RegisteredMFAMethodListFieldTest extends SapphireTest
{
    protected static $fixture_file = 'RegisteredMFAMethodListFieldTest.yml';

    public function testSchemaContainsEndpoints()
    {
        $memberID = $this->logInWithPermission();

        $field = new RegisteredMFAMethodListField('test', null, $memberID);
        $schema = $field->getSchemaDataDefaults();

        $this->assertStringContainsString('register/', $schema['schema']['endpoints']['register']);
        $this->assertStringContainsString('method/{urlSegment}', $schema['schema']['endpoints']['remove']);
        $this->assertStringContainsString('method/{urlSegment}/default', $schema['schema']['endpoints']['setDefault']);
    }

    public function testConstructorRequiresMemberValue()
    {
        $this->expectException(TypeError::class);
        new RegisteredMFAMethodListField('test', null, null);
    }
}
