<?php

namespace SilverStripe\MFA\Tests\Extension;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\Form;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\ORM\FieldType\DBDateTime;
use SilverStripe\Security\Member;

class MemberExtensionTest extends SapphireTest
{
    protected static $fixture_file = 'MemberExtensionTest.yml';

    protected static $required_extensions = [
        Member::class => [MemberExtension::class]
    ];

    public function testAdminUserCanViewButNotEditOthersMFAConfig()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'squib');

        $this->logInAs('mfaadmin');

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertFalse($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testAdminUserCanViewAndEditTheirOwnMFAConfig()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'mfaadmin');

        $this->logInAs($targetMember);

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertTrue($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testStandardUserCannotViewOrEditOthersMFAConfig()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'mfaadmin');

        $this->logInAs('squib');

        $this->assertFalse($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertFalse($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testStandardUserCanViewAndEditTheirOwnMFAConfig()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'squib');

        $this->logInAs($targetMember);

        $this->assertTrue($targetMember->currentUserCanViewMFAConfig(), 'Can View');
        $this->assertTrue($targetMember->currentUserCanEditMFAConfig(), 'Can Edit');
    }

    public function testAdminCanRequirePasswordChangeOnNextLogIn()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'squib');
        $this->logInWithPermission('ADMIN');
        $field = $targetMember->getCMSFields()->dataFieldByName('RequirePasswordChangeOnNextLogin');
        $this->assertNotNull($field);
    }

    public function testUserCannotRequireTheirOwnPasswordChangeOnNextLogIn()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'squib');
        $this->logInAs($targetMember);
        $field = $targetMember->getCMSFields()->dataFieldByName('RequirePasswordChangeOnNextLogin');
        $this->assertNull($field);
    }

    public function testUserCannotRequireOthersToPasswordChangeOnNextLogIn()
    {
        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'mfaadmin');
        $this->logInAs('squib');
        $field = $targetMember->getCMSFields()->dataFieldByName('RequirePasswordChangeOnNextLogin');
        $this->assertNull($field);
    }

    public function testCheckingRequirePasswordChangeOnNextLoginWillSetPasswordExpiryToNow()
    {
        $mockDate = '2019-03-02 00:00:00';
        DBDateTime::set_mock_now($mockDate);

        /** @var Member&MemberExtension $targetMember */
        $targetMember = $this->objFromFixture(Member::class, 'squib');

        $this->assertNull($targetMember->PasswordExpiry);

        $this->logInWithPermission('ADMIN');
        $fields = $targetMember->getCMSFields();
        $form = new Form(null, 'SomeForm', $fields, new FieldList());
        $field = $fields->dataFieldByName('RequirePasswordChangeOnNextLogin');
        $field->setValue(1);
        $form->saveInto($targetMember);

        $this->assertEquals($mockDate, $targetMember->PasswordExpiry);
    }
}
