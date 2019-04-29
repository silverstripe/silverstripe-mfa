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

        $schemaGenerator = SchemaGenerator::create();

        return array_merge($defaults, [
            'methods' => $schemaGenerator->getMethodSchema($this->value),
        ]);
    }
}
