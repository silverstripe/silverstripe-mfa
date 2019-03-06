<?php

namespace SilverStripe\MFA\Extension;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\DatetimeField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TextField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBDate;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\View\Requirements;

/**
 * Adds multi factor authentication related settings to the SiteConfig "Access" tab
 */
class SiteConfigExtension extends DataExtension
{
    /**
     * A URL that will help CMS users find out more information about multi factor authentication
     *
     * @config
     * @var string
     */
    private static $mfa_help_link = 'https://userhelp.silverstripe.org/en/4/';

    private static $db = [
        'MFAEnabled' => 'Boolean',
        'MFARequired' => 'Boolean',
        'MFAGracePeriodExpires' => 'Date',
    ];

    private static $defaults = [
        'MFARequired' => false,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        Requirements::javascript('silverstripe/mfa: client/dist/js/bundle-cms.js');
        Requirements::css('silverstripe/mfa: client/dist/styles/bundle-cms.css');

        $mfaEnabled = CheckboxField::create(
            'MFAEnabled',
            _t(__CLASS__ . '.MFA_ENABLED', 'Enable MFA for CMS access')
        );
        $mfaEnabled->addExtraClass('mfa-settings__enabled');

        $mfaOptions = OptionsetField::create(
            'MFARequired',
            '',
            [
                false => _t(__CLASS__ . '.MFA_OPTIONAL', 'MFA is optional for everyone'),
                true => _t(__CLASS__ . '.MFA_REQUIRED', 'MFA is required for everyone'),

            ]
        );
        $mfaOptions->addExtraClass('mfa-settings__required mfa-settings--hidden');

        $mfaGraceEnd = DateField::create(
            'MFAGracePeriodExpires',
            'Optional: grace period end date when MFA is enforced'
        );
        $mfaGraceEnd
            ->addExtraClass('mfa-settings__grace-period')
            // Don't allow users to set the date to anything earlier than now
            ->setMinDate(DBDatetime::now()->Format(DBDate::ISO_DATE));

        $mfaOptions = CompositeField::create($mfaEnabled, $mfaOptions, $mfaGraceEnd)
            ->setTitle(DBField::create_field(
                'HTMLFragment',
                _t(__CLASS__ . '.MULTI_FACTOR_AUTHENTICATION', 'Multi Factor Authentication (MFA)')
                . $this->getHelpLink()
            ));

        $fields->addFieldToTab('Root.Access', $mfaOptions);
    }

    /**
     * Gets an anchor tag for CMS users to click to find out more about MFA in the SilverStripe CMS
     *
     * @return string
     */
    protected function getHelpLink()
    {
        $link = $this->owner->config()->get('mfa_help_link');
        if (!$link) {
            return '';
        }

        return sprintf(
            '<a class="d-block mfa-settings__help-link" target="blank" rel="noopener" href="%s">%s</a>',
            $link,
            _t(__CLASS__ . '.FIND_OUT_MORE', 'Find out more')
        );
    }
}
