<?php
namespace SilverStripe\MFA\BasicMath;

use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\MethodRegisterHandler;

class Method implements MethodInterface
{
    /**
     * Return the LoginHandler that is used to start and verify login attempts with this method
     *
     * @return LoginHandlerInterface
     */
    public function getLoginHandler()
    {
        return new MethodLoginHandler();
    }

    /**
     * Return the RegisterHandler that is used to perform registrations with this method
     *
     * @return RegisterHandlerInterface
     */
    public function getRegisterHandler()
    {
        return new MethodRegisterHandler();
    }
}
