<?php
namespace SilverStripe\MFA\Extension;

use SilverStripe\Forms\FieldList;
use SilverStripe\MFA\FormField\RegisteredMFAMethodListField;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\ORM\DataExtension;
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

        if (!$this->currentUserCanViewMFAConfig()) {
            return $fields;
        }

        $fields->addFieldToTab(
            'Root.Main',
            $methodListField = RegisteredMFAMethodListField::create(
                'MFASettings',
                _t(__CLASS__ . '.MFA_SETTINGS_FIELD_LABEL', 'Multi-factor Authentication Settings (MFA)'),
                $this->owner
            )
        );

        // Administrators can't modify registered method
        if ($this->currentUserCanEditMFAConfig()) {
            $methodListField->setReadonly(true);
        }

        return $fields;
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
     * @return bool
     */
    public function currentUserCanEditMFAConfig()
    {
        return (Security::getCurrentUser() && Security::getCurrentUser()->ID === $this->owner->ID);
    }

    /**
     * Return a map of permission codes to add to the dropdown shown in the Security section of the CMS.
     * array(
     *   'VIEW_SITE' => 'View the site',
     * );
     */
    public function providePermissions()
    {
        $label = _t(
            __CLASS__ . '.MFA_PERMISSION_LABEL',
            'View / Reset MFA Configuration for other Members'
        );

        return [
            self::MFA_ADMINISTER_REGISTERED_METHODS => $label,
        ];
    }
}
