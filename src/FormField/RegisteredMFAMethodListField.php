<?php

namespace SilverStripe\MFA\FormField;

use SilverStripe\Forms\FormField;
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
            'schema' => SchemaGenerator::create()->getSchema($this->value),
        ]);
    }
}
