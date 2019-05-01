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

        $fullSchema = (SchemaGenerator::create())->getSchema($this->value);

        // Only pass down the parts of the schema that are relevant
        $methodData = [
            'registeredMethods' => $fullSchema['registeredMethods'],
            'availableMethods' => $fullSchema['availableMethods'],
            'defaultMethod' => $fullSchema['defaultMethod'],
            'backupMethod' => $fullSchema['backupMethod'],
        ];

        return array_merge($defaults, [
            'methods' => $methodData,
        ]);
    }
}
