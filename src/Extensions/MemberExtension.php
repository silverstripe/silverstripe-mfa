<?php

namespace Firesphere\BootstrapMFA;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
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
        $fields->removeByName(['Backupcodes']);
        $session = Controller::curr()->getRequest()->getSession();
        if ($session->get('tokens')) {
            $rootTabSet = $fields->fieldByName("Root");
            $field = LiteralField::create('tokens', $session->get('tokens'));
            $tab = Tab::create(
                'BackupTokens',
                'Backup Tokens'
            );
            $rootTabSet->push(
                $tab
            );
            $fields->addFieldToTab('Root.BackupTokens', $field);
            $session->clear('tokens');
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->owner->updateMFA) {
            Injector::inst()->get(BootstrapMFAProvider::class)->updateTokens();
        }
    }
}
