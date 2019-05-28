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

        return array_merge($defaults, [
            'schema' => SchemaGenerator::create()->getSchema($this->value) + [
                'endpoints' => [
                    'register' => AdminRegistrationController::singleton()->Link('register/{urlSegment}'),
                ],
                'resetEndpoint' => SecurityAdmin::singleton()->Link("reset/{$this->value->ID}"),
            ],
        ]);
    }
}
