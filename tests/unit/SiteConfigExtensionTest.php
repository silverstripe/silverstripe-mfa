<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Extensions\SiteConfigExtension;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverStripe\SiteConfig\SiteConfig;

class SiteConfigExtensionTest extends SapphireTest
{
    public function testUpdateCMSFields()
    {
        $fields = SiteConfig::current_site_config()->getCMSFields();

        $this->assertInstanceOf(CheckboxField::class, $fields->dataFieldByName('EnforceMFA'));
        $this->assertNull($fields->dataFieldByName('ForceMFA'));

        $config = SiteConfig::current_site_config();

        $config->ForceMFA = DBDatetime::now()->Format(DBDatetime::ISO_DATE);

        $config->write();

        $fields = SiteConfig::current_site_config()->getCMSFields();

        $this->assertInstanceOf(CheckboxField::class, $fields->dataFieldByName('EnforceMFA'));
        $this->assertContains('MFA enforced since ', $fields->dataFieldByName('EnforceMFA')->getDescription());
    }

    public function testUpdateCheckboxDescription()
    {
        /** @var SiteConfig|SiteConfigExtension $config */
        $config = SiteConfig::current_site_config();
        $config->ForceMFA = null;

        $this->assertNull($config->updateCheckboxDescription());
        $this->assertEmpty($config->getCMSFields()->dataFieldByName('EnforceMFA')->getDescription());

        $config->ForceMFA = DBDatetime::now()->Format(DBDatetime::ISO_DATE);

        $fields = FieldList::create([
            CheckboxField::create(
                'EnforceMFA',
                _t(self::class . '.ENFORCEMFA', 'Enforce MFA on all users')
            )
        ]);
        $config->updateCheckboxDescription($fields);

        $this->assertContains('MFA enforced since ', $fields->dataFieldByName('EnforceMFA')->getDescription());
    }

    public function testSaveEnforceMFA()
    {
        /** @var SiteConfig|SiteConfigExtension $config */
        $config = SiteConfig::current_site_config();

        $date = DBDatetime::now()->Format(DBDatetime::ISO_DATE);

        $config->saveEnforceMFA($date);

        $this->assertEquals($date, $config->ForceMFA);

        $config->saveEnforceMFA(false);

        $this->assertNull($config->ForceMFA);
    }
}
