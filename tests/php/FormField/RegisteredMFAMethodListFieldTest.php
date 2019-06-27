<?php

namespace SilverStripe\MFA\Tests\FormField;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\MFA\FormField\RegisteredMFAMethodListField;
use SilverStripe\Security\Member;
use TypeError;

class RegisteredMFAMethodListFieldTest extends SapphireTest
{
    protected static $fixture_file = 'RegisteredMFAMethodListFieldTest.yml';

    public function testSchemaContainsEndpoints()
    {
        $memberID = $this->logInWithPermission();
        $member = Member::get()->byID($memberID);

        $field = new RegisteredMFAMethodListField('test', null, $member);
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

    public function testSetValueOnlyAcceptsMemberObjects()
    {
        $memberID = $this->logInWithPermission();
        $member = Member::get()->byID($memberID);

        $field = new RegisteredMFAMethodListField('test', null, $member);
        $this->assertSame($member->ID, $field->Value()->ID);

        $field->setValue(null);
        $this->assertSame($member->ID, $field->Value()->ID, 'Value should remain unchanged after setting NULL');

        $anotherUser = $this->objFromFixture(Member::class, 'another-user');
        $field->setValue($anotherUser);
        $this->assertSame($anotherUser->ID, $field->Value()->ID, 'Value updates when setting a Member');
    }
}
