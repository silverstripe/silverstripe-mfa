<?php

namespace SilverStripe\MFA\FormField;

use FormField;
use Member;
use MFARegisteredMethod as RegisteredMethod;
use SecurityAdmin;
use SilverStripe\MFA\Controller\AdminRegistrationController;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Service\SchemaGenerator;

class RegisteredMFAMethodListField extends FormField
{
    public function Field($properties = array())
    {
        return $this->renderWith('RegisteredMFAMethodListField');
    }

    /**
     * @return array
     */
    public function getSchemaData()
    {
        $adminController = AdminRegistrationController::singleton();
        $generator = SchemaGenerator::create();

        return json_encode([
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
            'readOnly' => $this->isReadonly(),
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
        return RegisteredMethodManager::singleton()->getFromMember(Member::currentUser(), $backupMethod);
    }

    public function Type()
    {
        return 'RegisteredMFAMethodList';
    }
}
