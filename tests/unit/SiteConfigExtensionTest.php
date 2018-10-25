<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Extensions\SiteConfigExtension;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\SiteConfig\SiteConfig;

class SiteConfigExtensionTest extends SapphireTest
{
    public function testOnBeforeWrite()
    {
        /** @var SiteConfig|SiteConfigExtension $config */
        $config = SiteConfig::current_site_config();

        $config->setEnforceMFA(false);
        $config->ForceMFA = '0000-00-00';

        $config->write();

        $config = SiteConfig::current_site_config();

        $this->assertNull($config->ForceMFA);

        $config->setEnforceMFA(true);

        $config->write();

        $config = SiteConfig::current_site_config();

        $this->assertEquals(date('Y-m-d'), $config->ForceMFA);

        $config->setEnforceMFA(false);

        $config->write();

        $this->assertNull($config->ForceMFA);
    }

    public function testUpdateCMSFields()
    {
        $fields = SiteConfig::current_site_config()->getCMSFields();

        $this->assertInstanceOf(CheckboxField::class, $fields->dataFieldByName('EnforceMFA'));
        $this->assertNull($fields->dataFieldByName('ForceMFA'));

        $config = SiteConfig::current_site_config();

        $config->EnforceMFA = true;

        $config->write();

        $fields = SiteConfig::current_site_config()->getCMSFields();

        $this->assertInstanceOf(ReadonlyField::class, $fields->dataFieldByName('ForceMFA'));

    }

}
