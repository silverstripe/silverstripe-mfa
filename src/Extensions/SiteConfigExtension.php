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

/**
 * Class \Firesphere\BootstrapMFA\Extensions\SiteConfigExtension
 *
 * @property \Firesphere\BootstrapMFA\Extensions\SiteConfigExtension $owner
 * @property boolean $ForceMFA
 */
class SiteConfigExtension extends DataExtension
{

    private static $db = [
        'ForceMFA' => 'Boolean(false)'
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.MFA', CheckboxField::create('ForceMFA', 'Enforce MFA on all users'));
    }
}