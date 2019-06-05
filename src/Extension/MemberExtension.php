<?php

namespace SilverStripe\MFA\Extension;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\MFA\Authenticator\ChangePasswordHandler;
use SilverStripe\MFA\FormField\RegisteredMFAMethodListField;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;

/**
 * Extend Member to add relationship to registered methods and track some specific preferences
 *
 * @method RegisteredMethod[]|HasManyList RegisteredMFAMethods
 * @property MethodInterface DefaultRegisteredMethod
 * @property string DefaultRegisteredMethodID
 * @property bool HasSkippedMFARegistration
 * @property Member|MemberExtension owner
 */
class MemberExtension extends DataExtension implements PermissionProvider
{
    const MFA_ADMINISTER_REGISTERED_METHODS = 'MFA_ADMINISTER_REGISTERED_METHODS';

    private static $has_many = [
        'RegisteredMFAMethods' => RegisteredMethod::class,
    ];

    private static $db = [
        'DefaultRegisteredMethodID' => 'Int',
        'HasSkippedMFARegistration' => 'Boolean',
    ];

    /**
     * Accessor for the `DefaultRegisteredMethod` property
     *
     * This is replicating the usual functionality of a has_one relation but does it like this so we can ensure the same
     * instance of the MethodInterface is provided regardless if you access it through the has_one or the has_many.
     *
     * @return MethodInterface
     */
    public function getDefaultRegisteredMethod()
    {
        return $this->owner->RegisteredMFAMethods()->byId($this->owner->DefaultRegisteredMethodID);
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(['DefaultRegisteredMethodID', 'HasSkippedMFARegistration', 'RegisteredMFAMethods']);

        if (!$this->owner->exists() || !$this->currentUserCanViewMFAConfig()) {
            return $fields;
        }

        $fields->addFieldToTab(
            'Root.Main',
            $methodListField = RegisteredMFAMethodListField::create(
                'MFASettings',
                _t(__CLASS__ . '.MFA_SETTINGS_FIELD_LABEL', 'Multi Factor Authentication settings (MFA)'),
                $this->owner
            )
        );

        if (!$this->currentUserCanEditMFAConfig()) {
            // Only the member themeselves should be able to modify their MFA settings
            $methodListField->setReadonly(true);

            // We can allow an admin to require a user to change their password however. But:
            // - Don't show a read only field if the user cannot edit this record
            // - Don't show if a user views their own profile (just let them reset their own password)
            if ($this->owner->canEdit()) {
                $requireNewPassword = CheckboxField::create(
                    'RequirePasswordChangeOnNextLogin',
                    _t(__CLASS__ . 'RequirePasswordChangeOnNextLogin', 'Require password change on next login')
                );
                $fields->insertAfter('Password', $requireNewPassword);

                $fields->dataFieldByName('Password')->addExtraClass('form-group--no-divider');
            }
        }

        return $fields;
    }

    /**
     * Set password expiry to now to enforce a change of password next log in
     *
     * @param int|null $dataValue boolean representation checked/not checked {@see CheckboxField::dataValue}
     * @return Member
     */
    public function saveRequirePasswordChangeOnNextLogin($dataValue)
    {
        if ($dataValue && $this->owner->canEdit()) {
            // An expired password automatically requires a password change on logging in
            $this->owner->PasswordExpiry = DBDatetime::now()->Rfc2822();
        }
        return $this->owner;
    }

    /**
     * Determines whether the logged in user has sufficient permission to see the MFA config for this Member.
     *
     * @return bool
     */
    public function currentUserCanViewMFAConfig()
    {
        return (Permission::check(self::MFA_ADMINISTER_REGISTERED_METHODS)
            || $this->currentUserCanEditMFAConfig());
    }

    /**
     * Determines whether the logged in user has sufficient permission to modify the MFA config for this Member.
     * Note that this is different from being able to _reset_ the config (which administrators can do).
     *
     * @return bool
     */
    public function currentUserCanEditMFAConfig()
    {
        return (Security::getCurrentUser() && Security::getCurrentUser()->ID === $this->owner->ID);
    }

    /**
     * Provides the MFA view/reset permission for selection in the permission list in the CMS.
     *
     * @return array
     */
    public function providePermissions()
    {
        $label = _t(
            __CLASS__ . '.MFA_PERMISSION_LABEL',
            'View/reset MFA configuration for other members'
        );

        $category = _t(
            'SilverStripe\\Security\\Permission.PERMISSIONS_CATEGORY',
            'Roles and access permissions'
        );

        $description = _t(
            __CLASS__ . '.MFA_PERMISSION_DESCRIPTION',
            'Ability to view and reset registered MFA methods for other members.'
            . ' Requires the "Access to \'Security\' section" permission.'
        );

        return [
            self::MFA_ADMINISTER_REGISTERED_METHODS => [
                'name' => $label,
                'category' => $category,
                'help' => $description,
                'sort' => 200,
            ],
        ];
    }

    /**
     * Clear any temporary multi-factor authentication related session keys when a member is successfully logged in.
     */
    public function afterMemberLoggedIn()
    {
        if (!Controller::has_curr()) {
            return;
        }

        Controller::curr()
            ->getRequest()
            ->getSession()
            ->clear(ChangePasswordHandler::MFA_VERIFIED_ON_CHANGE_PASSWORD);
    }
}
