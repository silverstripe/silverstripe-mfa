<?php

namespace SilverStripe\MFA\FormField;

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
     * Because {@see setValue()} can do nothing on e.g. form submission, it is of critical importance that this
     * object is never in existence without a valid Member set as the value - we have set the `$value` parameter as
     * required, however we still need to ensure that it is a valid value. We do this via type hints, and removing the
     * optionality of the parameters for this constructor
     */
    public function __construct(string $name, ?string $title, Member $value)
    {
        parent::__construct($name, $title, $value);
    }

    public function setValue($value, $data = null)
    {
        // When a form submits, it populates data from POST values.
        // This field is not really a form field - it is a React JS component that renders interactive controls
        // for a member to manage their MFA settings. It therefore does not have a value, and `null` is not a valid
        // instance of Member - this causes a PHP Emeregency error (resulting in a HTTP 500). To this end, only ever
        // set the value if the value passed in is an instance of Member.
        if ($value instanceof Member) {
            $this->value = $value;
        }
        return $this;
    }

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
