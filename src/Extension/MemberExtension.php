<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Extension;

use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\MFA\Authenticator\ChangePasswordHandler;
use SilverStripe\MFA\Exception\InvalidMethodException;
use SilverStripe\MFA\FormField\RegisteredMFAMethodListField;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\HasManyList;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\PermissionProvider;
use SilverStripe\Security\Security;

/**
 * Extend Member to add relationship to registered methods and track some specific preferences
 *
 * @property MethodInterface $DefaultRegisteredMethod
 * @property string $DefaultRegisteredMethodID
 * @property bool $HasSkippedMFARegistration
 * @method HasManyList<RegisteredMethod> RegisteredMFAMethods()
 *
 * @extends Extension<Member&static>
 */
class MemberExtension extends Extension implements PermissionProvider
{
    public const MFA_ADMINISTER_REGISTERED_METHODS = 'MFA_ADMINISTER_REGISTERED_METHODS';

    private static $has_many = [
        'RegisteredMFAMethods' => RegisteredMethod::class,
    ];

    private static $db = [
        'DefaultRegisteredMethodID' => 'Int',
        'HasSkippedMFARegistration' => 'Boolean',
    ];

    /**
     * Accessor for the `DefaultRegisteredMethod` property.
     *
     * This is replicating the usual functionality of a has_one relation but does it like this so we can ensure the same
     * instance of the MethodInterface is provided regardless if you access it through the has_one or the has_many.
     *
     * @return RegisteredMethod|null
     */
    public function getDefaultRegisteredMethod(): ?RegisteredMethod
    {
        return $this->owner->RegisteredMFAMethods()->byId($this->owner->DefaultRegisteredMethodID);
    }

    /**
     * Set the default registered method for the current member. Does not write the owner record.
     *
     * @param RegisteredMethod $registeredMethod
     * @return Member
     * @throws InvalidMethodException
     */
    public function setDefaultRegisteredMethod(RegisteredMethod $registeredMethod): Member
    {
        if ($registeredMethod->Member()->ID != $this->owner->ID) {
            throw new InvalidMethodException('The provided method does not belong to this member');
        }
        $this->owner->DefaultRegisteredMethodID = $registeredMethod->ID;
        return $this->owner;
    }

    /**
     * @return string
     */
    public function getDefaultRegisteredMethodName(): string
    {
        $method = $this->getDefaultRegisteredMethod();
        return $method ? $method->getMethod()->getName() : '';
    }

    /**
     * @return string
     */
    public function getRegisteredMethodNames(): string
    {
        $arr = array_map(function ($method) {
            return $method->getMethod()->getName();
        }, $this->owner->RegisteredMFAMethods()->toArray() ?? []);
        return implode(', ', $arr);
    }

    protected function updateCMSFields(FieldList $fields): FieldList
    {
        $fields->removeByName(['DefaultRegisteredMethodID', 'HasSkippedMFARegistration', 'RegisteredMFAMethods']);

        if (!$this->owner->exists() || !$this->currentUserCanViewMFAConfig()) {
            return $fields;
        }

        $fields->addFieldToTab(
            'Root.Main',
            $methodListField = RegisteredMFAMethodListField::create(
                'MFASettings',
                _t(__CLASS__ . '.MFA_SETTINGS_FIELD_LABEL', 'Multi-factor authentication settings (MFA)'),
                $this->owner->ID
            )
        );

        if (!$this->currentUserCanEditMFAConfig()) {
            $methodListField->setReadonly(true);
        }

        return $fields;
    }

    /**
     * Determines whether the logged in user has sufficient permission to see the MFA config for this Member.
     *
     * @return bool
     */
    public function currentUserCanViewMFAConfig(): bool
    {
        return (Permission::check(MemberExtension::MFA_ADMINISTER_REGISTERED_METHODS)
            || $this->currentUserCanEditMFAConfig());
    }

    /**
     * Determines whether the logged in user has sufficient permission to modify the MFA config for this Member.
     * Note that this is different from being able to _reset_ the config (which administrators can do).
     *
     * @return bool
     */
    public function currentUserCanEditMFAConfig(): bool
    {
        return (Security::getCurrentUser() && Security::getCurrentUser()->ID === $this->owner->ID);
    }

    /**
     * Provides the MFA view/reset permission for selection in the permission list in the CMS.
     *
     * @return array
     */
    public function providePermissions(): array
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
            MemberExtension::MFA_ADMINISTER_REGISTERED_METHODS => [
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
    protected function onAfterMemberLoggedIn(): void
    {
        $controller = Controller::curr();
        if (!$controller) {
            return;
        }
        $controller
            ->getRequest()
            ->getSession()
            ->clear(ChangePasswordHandler::MFA_VERIFIED_ON_CHANGE_PASSWORD);
    }
}
