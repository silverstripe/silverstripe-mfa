<?php

namespace Firesphere\BootstrapMFA\Tests\Helpers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\Session;

class CodeHelper
{
    /**
     * Get the codes from the session for testing reasons
     *
     * @return array
     */
    public static function getCodesFromSession()
    {
        // Funky stuff, extract the codes from the session message
        /** @var Session $session */
        $session = Controller::curr()->getRequest()->getSession();

        $message = $session->get('tokens');

        $message = str_replace('<p>Here are your tokens, please store them securily. ' .
            'They are stored encrypted and can not be recovered, only reset.</p><p>', '', $message);
        $codes = explode('<br />', $message);

        // Remove the <p> at the end
        array_pop($codes);

        return $codes;
    }
}
