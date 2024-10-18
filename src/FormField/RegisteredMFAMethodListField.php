<?php

namespace SilverStripe\MFA\FormField;

use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Forms\FormField;
use SilverStripe\MFA\Controller\AdminRegistrationController;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Service\SchemaGenerator;
use SilverStripe\ORM\DataObject;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

class RegisteredMFAMethodListField extends FormField
{
    /**
     * {@inheritDoc}
     *
     * @param string      $name  Field name
     * @param string|null $title Field title
     * @param int         $value Member ID to apply this field to
     */
    public function __construct(string $name, ?string $title, int $value)
    {
        parent::__construct($name, $title, $value);
    }

    public function Field($properties = array())
    {
        return $this->renderWith(RegisteredMFAMethodListField::class);
    }

    /**
     * @return array
     */
    public function getSchemaDataDefaults()
    {
        $defaults = parent::getSchemaDataDefaults();

        $adminController = AdminRegistrationController::singleton();
        $generator = SchemaGenerator::create();

        if (!$this->value && $this->getForm() && $this->getForm()->getRecord() instanceof Member) {
            $member = $this->getForm()->getRecord();
        } else {
            $member = DataObject::get_by_id(Member::class, $this->value);
        }

        return array_merge($defaults, [
            'schema' => $generator->getSchema($member) + [
                'endpoints' => [
                    'register' => $adminController->Link('register/{urlSegment}'),
                    'remove' => $adminController->Link('method/{urlSegment}'),
                    'setDefault' => $adminController->Link('method/{urlSegment}/default'),
                ],
                // We need all available methods so we can re-register pre-existing methods
                'allAvailableMethods' => $generator->getAvailableMethods(),
                'backupCreatedDate' => $this->getBackupMethod($member)
                    ? $this->getBackupMethod($member)->Created
                    : null,
                'resetEndpoint' => SecurityAdmin::singleton()->Link("users/reset/{$this->value}"),
                'isMFARequired' => EnforcementManager::create()->isMFARequired(),
            ],
        ]);
    }

    /**
     * Get the registered backup method (if any) from the currently logged in user.
     *
     * @return RegisteredMethod|null
     */
    protected function getBackupMethod($member = null): ?RegisteredMethod
    {
        $backupMethod = MethodRegistry::singleton()->getBackupMethod();
        return RegisteredMethodManager::singleton()->getFromMember($member ?? Security::getCurrentUser(), $backupMethod);
    }
}
