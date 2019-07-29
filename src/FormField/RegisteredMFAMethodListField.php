<?php

namespace SilverStripe\MFA\FormField;

use InvalidArgumentException;
use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Forms\FormField;
use SilverStripe\MFA\Controller\AdminRegistrationController;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\Service\SchemaGenerator;
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
        /** @var Member $member */
        $member = Member::get()->byID($this->value);

        return array_merge($defaults, [
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
