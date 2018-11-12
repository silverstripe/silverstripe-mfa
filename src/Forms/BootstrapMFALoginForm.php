<?php

namespace Firesphere\BootstrapMFA\Forms;

use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

/**
 * Class BootstrapMFALoginForm
 *
 * @package Firesphere\BootstrapMFA\Forms
 */
class BootstrapMFALoginForm extends MemberLoginForm
{
    /**
     * @todo make this a lot better!
     * @return FieldList
     */
    public function getFormFields()
    {
        $fields = parent::getFormFields();
        $session = $this->controller->getRequest()->getSession();
        if ($session->get('tokens')) {
            $field = LiteralField::create('tokens', $session->get('tokens'));
            $fields->push($field);
            $session->clear('tokens');
        }

        return $fields;
    }
}
