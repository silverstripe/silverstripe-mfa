<?php

namespace SilverStripe\MFA\FormField;

use SilverStripe\Admin\SecurityAdmin;
use SilverStripe\Forms\FormField;
use SilverStripe\MFA\Controller\AdminRegistrationController;
use SilverStripe\MFA\Service\SchemaGenerator;

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
                    'remove' => $adminController->Link('remove/{urlSegment}'),
                ],
                // We need all available methods so we can re-register pre-existing methods
                'allAvailableMethods' => $generator->getAvailableMethods(),
                'resetEndpoint' => SecurityAdmin::singleton()->Link("reset/{$this->value->ID}"),
            ],
        ]);
    }
}
