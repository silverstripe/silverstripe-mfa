<?php

namespace Firesphere\BootstrapMFA\Extensions;

use Firesphere\BootstrapMFA\Config\MFAEnabledFields;
use Firesphere\BootstrapMFA\Models\BackupCode;
use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DataExtension;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class MemberExtension
 *
 * @package Firesphere\BootstrapMFA
 * @property \Firesphere\BootstrapMFA\Extensions\MemberExtension $owner
 * @property boolean $MFAEnabled
 * @method \SilverStripe\ORM\DataList|\Firesphere\BootstrapMFA\Models\BackupCode[] Backupcodes()
 */
class MemberExtension extends DataExtension
{
    use Configurable;

    private static $db = [
        'MFAEnabled' => 'Boolean(false)',
    ];
    private static $has_many = [
        'Backupcodes' => BackupCode::class
    ];

    protected $updateMFA = false;

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldToTab('Root.Main', $enabled = CheckboxField::create('MFAEnabled', 'MFA Enabled'));
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

    public function onBeforeWrite()
    {
        if (SiteConfig::current_site_config()->ForceMFA && !$this->owner->MFAEnableds) {
            $this->owner->MFAEnabled = true;
            $this->owner->updateMFA = true;
        }
    }

    public function onAfterWrite()
    {
        parent::onAfterWrite();
        if ($this->owner->updateMFA) {
            $provider = Injector::inst()->get(BootstrapMFAProvider::class);
            $provider->setMember($this->owner);
            $provider->updateTokens();
        }
    }
}
