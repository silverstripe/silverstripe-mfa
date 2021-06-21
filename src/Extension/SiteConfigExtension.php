<?php

namespace SilverStripe\MFA\Extension;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\ListboxField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Security\Group;
use SilverStripe\View\Requirements;

/**
 * Adds multi-factor authentication related settings to the SiteConfig "Access" tab
 *
 * @property bool MFARequired
 * @property string MFAGracePeriodExpires
 */
class SiteConfigExtension extends DataExtension
{
    /**
     * A URL that will help CMS users find out more information about multi-factor authentication
     *
     * @config
     * @var string
     */
    // phpcs:disable
    private static $mfa_help_link = 'https://userhelp.silverstripe.org/en/4/optional_features/multi-factor_authentication/';
    // phpcs:enable

    private static $db = [
        'MFARequired' => 'Boolean',
        'MFAGracePeriodExpires' => 'Date',
    ];

    private static $many_many = [
      'MFAGroupRestrictions' => Group::class
    ];

    private static $defaults = [
        'MFARequired' => false,
    ];

    public function updateCMSFields(FieldList $fields)
    {
        Requirements::javascript('silverstripe/mfa: client/dist/js/bundle-cms.js');
        Requirements::css('silverstripe/mfa: client/dist/styles/bundle-cms.css');

        $mfaOptions = OptionsetField::create(
            'MFARequired',
            '',
            [
                false => _t(__CLASS__ . '.MFA_OPTIONAL', 'MFA is optional for everyone'),
                true => _t(__CLASS__ . '.MFA_REQUIRED', 'MFA is required for everyone'),
            ]
        );
        $mfaOptions->addExtraClass('mfa-settings__required');

        $mfaGraceEnd = DateField::create(
            'MFAGracePeriodExpires',
            _t(__CLASS__ . '.MFA_GRACE_TITLE', 'MFA will be required from (optional)')
        );
        $mfaGraceEnd->setDescription(_t(
            __CLASS__ . '.MFA_GRACE_DESCRIPTION',
            'MFA setup will be optional prior to this date'
        ));
        $mfaGraceEnd->addExtraClass('mfa-settings__grace-period');

        $groupsMap = [];
        foreach (Group::get() as $group) {
            // Listboxfield values are escaped, use ASCII char instead of &raquo;
            $groupsMap[$group->ID] = $group->getBreadcrumbs(' > ');
        }
        asort($groupsMap);

        $mfaGroupRestrict = ListboxField::create(
            "MFAGroupRestrictions",
            _t(__CLASS__ . '.MFA_GROUP_RESTRICTIONS', "MFA Groups")
        )
            ->setSource($groupsMap)
            ->setAttribute(
                'data-placeholder',
                _t(__CLASS__ . '.MFA_GROUP_RESTRICTIONS_PLACEHOLDER', 'Click to select group')
            )
            ->setDescription(_t(
                __CLASS__ . '.MFA_GROUP_RESTRICTIONS_DESCRIPTION',
                'MFA will only be enabled for members of these selected groups. ' .
                'If no groups are selected, MFA will be enabled for all users'
            ));

        $mfaOptions = CompositeField::create($mfaOptions, $mfaGraceEnd, $mfaGroupRestrict)
            ->setTitle(DBField::create_field(
                'HTMLFragment',
                _t(__CLASS__ . '.MULTI_FACTOR_AUTHENTICATION', 'Multi-factor authentication (MFA)')
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
            _t(__CLASS__ . '.MFA_LEARN_MORE', 'Learn about MFA')
        );
    }
}
