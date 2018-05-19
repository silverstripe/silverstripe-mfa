<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Extensions\MemberExtension;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TabSet;

class SiteConfigExtensionTest extends SapphireTest
{

    public function testUpdateCMSFields()
    {
        $fields = FieldList::create([TabSet::create('Root')]);

        /** @var MemberExtension $extension */
        $extension = Injector::inst()->get(MemberExtension::class);

        $extension->updateCMSFields($fields);

        $this->assertInstanceOf(CheckboxField::class, $fields->dataFieldByName('ForceMFA'));
    }

}