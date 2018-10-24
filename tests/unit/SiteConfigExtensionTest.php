<?php

namespace Firesphere\BootstrapMFA\Tests;

use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\SiteConfig\SiteConfig;

class SiteConfigExtensionTest extends SapphireTest
{
    public function testUpdateCMSFields()
    {
        $fields = SiteConfig::current_site_config()->getCMSFields();

        $this->assertInstanceOf(CheckboxField::class, $fields->dataFieldByName('EnforceMFA'));
    }
}
