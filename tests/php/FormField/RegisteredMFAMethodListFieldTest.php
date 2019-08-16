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

        $this->assertContains('register/', $schema['schema']['endpoints']['register']);
        $this->assertContains('method/{urlSegment}', $schema['schema']['endpoints']['remove']);
        $this->assertContains('method/{urlSegment}/default', $schema['schema']['endpoints']['setDefault']);
    }

    /**
     * @expectedException TypeError
     */
    public function testConstructorRequiresMemberValue()
    {
        new RegisteredMFAMethodListField('test', null, null);
    }
}
