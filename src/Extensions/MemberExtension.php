<?php

namespace Firesphere\BootstrapMFA\Extensions;

use DateTime;
use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Models\BackupCode;
use Firesphere\BootstrapMFA\Providers\BootstrapMFAProvider;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataList;
use SilverStripe\Security\Member;
use SilverStripe\SiteConfig\SiteConfig;

/**
 * Class MemberExtension
 *
 * @package Firesphere\BootstrapMFA
 * @property Member|MemberExtension $owner
 * @property boolean $MFAEnabled
 * @property string $PrimaryMFA
 * @method DataList|BackupCode[] BackupCodes()
 */
class MemberExtension extends DataExtension
{
    /**
     * @var array
     */
    private static $db = [
        'MFAEnabled' => 'Boolean(false)',
        'PrimaryMFA' => 'Varchar(255)',
    ];

    /**
     * @var array
     */
    private static $has_many = [
        'BackupCodes' => BackupCode::class
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
        $this->updateMFA = false;
        $fields->removeByName(['BackupCodes', 'PrimaryMFA']);
        $session = Controller::curr()->getRequest()->getSession();
        $rootTabSet = $fields->fieldByName('Root');
        $field = LiteralField::create('tokens', $session->get('tokens'));
        // We need to push the tab for unit tests
        $tab = Tab::create(
            'MFA',
            _t(self::class . '.MFATAB', 'Multi Factor Authentication')
        );
        $rootTabSet->push(
            $tab
        );
        $fields->addFieldToTab(
            'Root.MFA',
            $enabled = CheckboxField::create('MFAEnabled', _t(self::class . '.MFAEnabled', 'MFA Enabled'))
        );
        $fields->addFieldToTab(
            'Root.MFA',
            CheckboxField::create('updateMFA', _t(self::class . '.RESETMFA', 'Reset MFA codes'))
        );

        if ($session->get('tokens')) {
            $fields->addFieldToTab('Root.MFA', $field);
            $session->clear('tokens');
        }
    }

    /**
     * Force enable MFA on the member if needed
     */
    public function onBeforeWrite()
    {
        if (!$this->owner->MFAEnabled && SiteConfig::current_site_config()->ForceMFA) {
            $this->owner->MFAEnabled = true;
            $this->owner->updateMFA = true;
        }
    }

    /**
     *
     * @throws \Psr\Container\NotFoundExceptionInterface
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

    public function getBackupcodes()
    {
        return $this->owner->BackupCodes();
    }

    public function isInGracePeriod()
    {
        /** @var Member|MemberExtension $member */
        $member = $this->owner;

        // If MFA is enabled on the member, we're always using it
        if ($member->MFAEnabled) {
            return false;
        }

        /** @var SiteConfig|SiteConfigExtension $config */
        $config = SiteConfig::current_site_config();
        // If MFA is not enforced, we're in an endless grace period
        if (!$config->ForceMFA) {
            return true;
        }

        $graceStartDay = ($member->Created > $config->ForceMFA) ? $member->Created : $config->ForceMFA;
        $graceStartDay = new DateTime($graceStartDay);

        $gracePeriod = Config::inst()->get(BootstrapMFAAuthenticator::class, 'grace_period');

        $nowDate = new DateTime(date('Y-m-d'));

        $diff = $nowDate->diff($graceStartDay)->format('%a');

        return !($diff >= $gracePeriod);
    }
}
