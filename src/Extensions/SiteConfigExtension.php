<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 17-May-18
 * Time: 19:17
 */

namespace Firesphere\BootstrapMFA\Extensions;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class \Firesphere\BootstrapMFA\Extensions\SiteConfigExtension
 *
 * @property SiteConfig|SiteConfigExtension $owner
 * @property boolean $ForceMFA
 */
class SiteConfigExtension extends DataExtension
{
    private static $db = [
        'ForceMFA' => 'Boolean(false)'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab(
            'Root.MFA',
            CheckboxField::create('ForceMFA', _t(self::class . '.ENFORCEMFA', 'Enforce MFA on all users'))
        );
    }
}
