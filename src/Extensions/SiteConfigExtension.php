<?php

namespace Firesphere\BootstrapMFA\Extensions;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ReadonlyField;
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
     * Is MFA Enforced via a comparison in {@link updateCMSFields()}
     *
     * @var bool
     */
    protected $EnforceMFA = false;

    /**
     * Add the checkbox and if enabled the date since enforcement
     *
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $this->EnforceMFA = !($this->owner->ForceMFA === null || $this->owner->ForceMFA === '0000-00-00');
        $fields->addFieldToTab(
            'Root.MFA',
            CheckboxField::create(
                'EnforceMFA',
                _t(self::class . '.ENFORCEMFA', 'Enforce MFA on all users'),
                $this->EnforceMFA
            )
        );
        if ($this->EnforceMFA) {
            $fields->addFieldToTab(
                'Root.MFA',
                ReadonlyField::create('ForceMFA', _t(self::class . '.ENFORCEDSINCE', 'MFA enforced since'))
            );
        }
    }

    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        // Edge case, when building from the previous Boolean, it'll set itself to 0000-00-00
        if ($this->owner->ForceMFA === '0000-00-00') {
            $this->owner->ForceMFA = null;
        }

        /* Set the MFA enforcement */
        if (!$this->owner->ForceMFA && $this->owner->EnforceMFA) {
            $this->owner->ForceMFA = DBDatetime::now()->Format('YYYY-MM-dd');
        }

        /* Reset the MFA enforcement if the checkbox is unchecked */
        if (!$this->owner->EnforceMFA) {
            $this->owner->ForceMFA = null;
        }
    }
}
