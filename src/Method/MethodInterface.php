<?php
namespace SilverStripe\MFA\Method;

use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;

/**
 * Defines an Authentication Method, which serves as an additional factor for authentication beyond the standard
 * username / password method.
 */
interface MethodInterface
{
    /**
     * Return the LoginHandler that is used to start and verify login attempts with this method
     *
     * @return LoginHandlerInterface
     */
    public function getLoginHandler();

    /**
     * Return the RegisterHandler that is used to perform registrations with this method
     *
     * @return RegisterHandlerInterface
     */
    public function getRegisterHandler();
}
