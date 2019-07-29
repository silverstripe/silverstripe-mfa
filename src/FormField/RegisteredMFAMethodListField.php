<?php

namespace SilverStripe\MFA\FormField;

use DataObject;
use FormField;
use Member;
use MFARegisteredMethod as RegisteredMethod;
use SecurityAdmin;
use SilverStripe\MFA\Controller\AdminRegistrationController;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Service\SchemaGenerator;

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
        return $this->renderWith('RegisteredMFAMethodListField');
    }

    /**
     * @return string
     */
    public function getSchemaData()
    {
        $adminController = AdminRegistrationController::singleton();
        $generator = SchemaGenerator::create();
        /** @var Member $member */
        $member = DataObject::get_by_id(Member::class, $this->value);

        return json_encode([
            'schema' => $generator->getSchema($member) + [
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
                'resetEndpoint' => SecurityAdmin::singleton()->Link("reset/{$this->value}"),
                'isMFARequired' => EnforcementManager::create()->isMFARequired(),
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
