<?php

namespace SilverStripe\MFA\Extension;

use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\CompositeField;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TreeMultiselectField;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\Group;
use SilverStripe\View\Requirements;

/**
 * Adds multi-factor authentication related settings to the SiteConfig "Access" tab
 *
 * @property bool $MFARequired
 * @property string $MFAGracePeriodExpires
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
        'MFAAppliesTo' => 'Enum(["'
            . EnforcementManager::APPLIES_TO_EVERYONE . '","'
            . EnforcementManager::APPLIES_TO_GROUPS . '"])',
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
                false => _t(__CLASS__ . '.MFA_OPTIONAL2', 'MFA is optional'),
                true => _t(__CLASS__ . '.MFA_REQUIRED2', 'MFA is required'),
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

        $mfaAppliesToWho = OptionsetField::create(
            'MFAAppliesTo',
            _t(__CLASS__ . '.MFA_APPLIES_TO_TITLE', 'Who do these MFA settings apply to?'),
            [
                EnforcementManager::APPLIES_TO_EVERYONE => _t(__CLASS__ . '.EVERYONE', 'Everyone'),
                EnforcementManager::APPLIES_TO_GROUPS => _t(
                    __CLASS__ . '.ONLY_GROUPS',
                    'Only these groups (choose from list)'
                ),
            ]
        );

        $mfaGroupRestrict = TreeMultiselectField::create(
            'MFAGroupRestrictions',
            _t(__CLASS__ . '.MFA_GROUP_RESTRICTIONS', 'MFA Groups'),
            Group::class
        )->setDescription(_t(
            __CLASS__ . '.MFA_GROUP_RESTRICTIONS_DESCRIPTION',
            'MFA will only be enabled for members of these selected groups.'
        ))->addExtraClass('js-mfa-group-restrictions');

        $mfaOptions = CompositeField::create($mfaOptions, $mfaGraceEnd, $mfaAppliesToWho, $mfaGroupRestrict)
            ->setTitle(DBField::create_field(
                'HTMLFragment',
                _t(__CLASS__ . '.MULTI_FACTOR_AUTHENTICATION', 'Multi-factor authentication (MFA)')
                . $this->getHelpLink()
            ));

        $fields->addFieldToTab('Root.Access', $mfaOptions);
    }

    public function validate(ValidationResult $validationResult)
    {
        if (
            $this->owner->MFAAppliesTo == EnforcementManager::APPLIES_TO_GROUPS
            && !$this->owner->MFAGroupRestrictions()->exists()
        ) {
            $validationResult->addFieldError(
                'MFAGroupRestrictions',
                _t(
                    __CLASS__ . '.MFA_GROUP_RESTRICTIONS_VALIDATION',
                    'At least one group must be selected, or the MFA settings should apply to everyone.'
                )
            );
        }
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
