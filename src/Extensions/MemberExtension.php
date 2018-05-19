<?php

namespace Firesphere\BootstrapMFA\Extensions;

use Firesphere\BootstrapMFA\Models\BackupCode;
use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Control\Controller;
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

    /**
     * @var array
     */
    private static $db = [
        'MFAEnabled' => 'Boolean(false)',
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'Backupcodes' => BackupCode::class
    ];

    /**
     * @var bool
     */
    protected $updateMFA = false;

    /**
     * @param FieldList $fields
     */
    public function updateCMSFields(FieldList $fields)
    {
        $fields->removeByName(['Backupcodes']);
        $session = Controller::curr()->getRequest()->getSession();
        $rootTabSet = $fields->fieldByName("Root");
        $field = LiteralField::create('tokens', $session->get('tokens'));
        $tab = Tab::create(
            'MFA',
            _t(__CLASS__ . '.MFATAB', 'Multi Factor Authentication')
        );
        $rootTabSet->push(
            $tab
        );
        $fields->addFieldToTab(
            'Root.MFA',
            $enabled = CheckboxField::create('MFAEnabled', _t(__CLASS__ . '.MFAEnabled', 'MFA Enabled'))
        );
        $fields->addFieldToTab(
            'Root.MFA',
            CheckboxField::create('updateMFA', _t(__CLASS__ . '.RESETMFA', 'Reset MFA codes'))
        );

        if ($session->get('tokens')) {
            $fields->addFieldToTab('Root.MFA', $field);
            $session->clear('tokens');
        }
    }

    /**
     *
     */
    public function onBeforeWrite()
    {
        if (SiteConfig::current_site_config()->ForceMFA && !$this->owner->MFAEnabled) {
            $this->owner->MFAEnabled = true;
            $this->owner->updateMFA = true;
        }
    }

    /**
     *
     */
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
