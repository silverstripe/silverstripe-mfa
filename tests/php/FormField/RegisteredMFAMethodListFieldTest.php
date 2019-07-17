<?php

namespace SilverStripe\MFA\Tests\FormField;

use SapphireTest;
use SilverStripe\MFA\FormField\RegisteredMFAMethodListField;
use Member;

class RegisteredMFAMethodListFieldTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testSchemaContainsEndpoints()
    {
        $memberID = $this->logInWithPermission();
        $member = Member::get()->byID($memberID);

        $field = new RegisteredMFAMethodListField('test');
        $field->setValue($member);
        $schema = json_decode($field->getSchemaData(), true);

        $this->assertContains('register/', $schema['schema']['endpoints']['register']);
        $this->assertContains('method/{urlSegment}', $schema['schema']['endpoints']['remove']);
        $this->assertContains('method/{urlSegment}/default', $schema['schema']['endpoints']['setDefault']);
    }
}
