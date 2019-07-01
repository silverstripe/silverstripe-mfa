<?php

namespace SilverStripe\MFA\FormField;

use SecurityAdmin;
use FormField;
use SilverStripe\MFA\Controller\AdminRegistrationController;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Service\SchemaGenerator;
use Security;

class RegisteredMFAMethodListField extends FormField
{
    public function Field($properties = array())
    {
        return $this->renderWith(self::class);
    }

    /**
     * @return array
     */
    public function getSchemaDataDefaults()
    {
        $defaults = parent::getSchemaDataDefaults();

        $adminController = AdminRegistrationController::singleton();
        $generator = SchemaGenerator::create();

        return array_merge($defaults, [
            'schema' => $generator->getSchema($this->value) + [
                'endpoints' => [
                    'register' => $adminController->Link('register/{urlSegment}'),
                    'remove' => $adminController->Link('method/{urlSegment}'),
                    'setDefault' => $adminController->Link('method/{urlSegment}/default'),
                ],
                // We need all available methods so we can re-register pre-existing methods
                'allAvailableMethods' => $generator->getAvailableMethods(),
                'backupCreationDate' => $this->getBackupMethod()
                    ? $this->getBackupMethod()->Created
                    : null,
                'resetEndpoint' => SecurityAdmin::singleton()->Link("reset/{$this->value->ID}"),
            ],
        ]);
    }

    /**
     * Get the registered backup method (if any) from the currently logged in user.
     *
     * @return RegisteredMethod|null
     */
    protected function getBackupMethod(): ?RegisteredMethod
    {
        $backupMethod = MethodRegistry::singleton()->getBackupMethod();
        return RegisteredMethodManager::singleton()->getFromMember(Security::getCurrentUser(), $backupMethod);
    }
}
