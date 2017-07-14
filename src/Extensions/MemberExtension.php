<?php

namespace Firesphere\BootstrapMFA;

use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\DataExtension;

/**
 * Class MemberExtension
 * @package Firesphere\BootstrapMFA
 *
 * @property string $Backupcodes
 */
class MemberExtension extends DataExtension
{

    private static $has_many = [
        'Backupcodes' => BackupCode::class
    ];

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', CheckboxField::create('updateMFA', 'Reset MFA codes'));
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if($this->owner->updateMFA) {
            Injector::inst()->get(BootstrapMFAProvider::class)->updateTokens();
        }
    }
}
