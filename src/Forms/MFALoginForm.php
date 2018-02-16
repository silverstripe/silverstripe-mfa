<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 14-Jul-17
 * Time: 22:34
 */

namespace Firesphere\BootstrapMFA;

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
