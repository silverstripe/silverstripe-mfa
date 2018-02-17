<?php

namespace Firesphere\BootstrapMFA\Forms;

use SilverStripe\Forms\LiteralField;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

class MFALoginForm extends MemberLoginForm
{
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
