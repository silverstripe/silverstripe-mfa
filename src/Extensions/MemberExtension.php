<?php

namespace Firesphere\BootstrapMFA\Extensions;

use Firesphere\BootstrapMFA\Models\BackupCode;
use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DataExtension;

/**
 * Class MemberExtension
 *
 * @package Firesphere\BootstrapMFA
 * @property \SilverStripe\Security\Member|\Firesphere\BootstrapMFA\MemberExtension $owner
 * @method \SilverStripe\ORM\DataList|\Firesphere\BootstrapMFA\BackupCode[] Backupcodes()
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
