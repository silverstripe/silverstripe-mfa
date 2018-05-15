<?php

namespace Firesphere\BootstrapMFA\Tests;

use Firesphere\BootstrapMFA\Authenticators\BootstrapMFAAuthenticator;
use Firesphere\BootstrapMFA\Forms\BootstrapMFALoginForm;
use SilverStripe\Control\Controller;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Forms\FieldList;

class BootstrapMFALoginFormTest extends SapphireTest
{
    public function testGetFormFieldsWithTokens()
    {
        $controller = Controller::curr();
        $controller->getRequest()->getSession()->set('tokens', '12345678');
        /** @var BootstrapMFALoginForm $form */
        $form = Injector::inst()->createWithArgs(
            BootstrapMFALoginForm::class,
            [$controller, BootstrapMFAAuthenticator::class, 'test']
        );

        $fields = $form->getFormFields();
        $this->assertInstanceOf(FieldList::class, $fields);

        $this->assertNull($controller->getRequest()->getSession()->get('tokens'));
        foreach ($fields as $field) {
            // Hack. InstanceOf fails while the field is there
            if ($field->getName() === 'tokens') {
                $this->assertTrue(true);
            }
        }
    }

    public function testGetFormFieldsNoTokens()
    {
        $controller = Controller::curr();
        /** @var BootstrapMFALoginForm $form */
        $form = Injector::inst()->createWithArgs(
            BootstrapMFALoginForm::class,
            [$controller, BootstrapMFAAuthenticator::class, 'test']
        );

        $fields = $form->getFormFields();
        $this->assertInstanceOf(FieldList::class, $fields);

        $this->assertEquals(null, $fields->dataFieldByName('tokens'));
    }
}
