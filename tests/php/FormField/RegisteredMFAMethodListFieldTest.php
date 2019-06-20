<?php

namespace SilverStripe\MFA\Tests\FormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\FormField\RegisteredMFAMethodListField;
use SilverStripe\Security\Member;

class RegisteredMFAMethodListFieldTest extends SapphireTest
{
    protected $usesDatabase = true;

    public function testSchemaContainsEndpoints()
    {
        $memberID = $this->logInWithPermission();
        $member = Member::get()->byID($memberID);

        $field = new RegisteredMFAMethodListField('test');
        $field->setValue($member);
        $schema = $field->getSchemaDataDefaults();

        $this->assertContains('register/', $schema['schema']['endpoints']['register']);
        $this->assertContains('remove/', $schema['schema']['endpoints']['remove']);
        $this->assertContains('setDefault/', $schema['schema']['endpoints']['setDefault']);
    }
}
