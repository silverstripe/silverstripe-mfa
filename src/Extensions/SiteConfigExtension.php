<?php

namespace Firesphere\BootstrapMFA\Extensions;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class \Firesphere\BootstrapMFA\Extensions\SiteConfigExtension
 *
 * @property SiteConfig|SiteConfigExtension $owner
 * @property string $ForceMFA
 */
class SiteConfigExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'ForceMFA' => 'Date'
    ];

    /**
     * Add the checkbox and if enabled the date since enforcement
     *
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab(
            'Root.MFA',
            $checkbox = CheckboxField::create(
                'EnforceMFA',
                _t(self::class . '.ENFORCEMFA', 'Enforce MFA on all users'),
                $this->isMFAEnforced()
            )
        );
        $checkbox->setDescription(null);

        $this->updateCheckboxDescription($fields);
    }

    protected function isMFAEnforced()
    {
        // "0000-00-00" provides BC support for version 1.0 of BootstrapMFA where this attribute was stored as a boolean
        return $this->owner->ForceMFA !== null && $this->owner->ForceMFA !== '0000-00-00';
    }

    public function updateCheckboxDescription(FieldList $fields = null)
    {
        if ($this->isMFAEnforced()) {
            if (!$fields) {
                $fields = $this->owner->getCMSFields();
            }

            $fields->dataFieldByName('EnforceMFA')->setDescription(_t(
                self::class . '.ENFORCEDSINCE',
                'MFA enforced since {date}',
                ['date' => $this->owner->obj('ForceMFA')->Nice()]
            ));
        }
    }

    /**
     * Magic method, {@see Form::saveInto()}
     *
     * @param string|null|false $value Date string
     */
    public function saveEnforceMFA($value)
    {
        $MFAEnforced = $this->isMFAEnforced();

        // If the value is truthy and we don't have MFA already enforced
        if ($value && !$MFAEnforced) {
            $this->owner->ForceMFA = DBDatetime::now()->Format(DBDatetime::ISO_DATE);
            // Otherwise if the field indicates MFA should not be enforced but it currently is
        } elseif (!$value && $MFAEnforced) {
            $this->owner->ForceMFA = null;
        }

        $this->updateCheckboxDescription();
    }
}
