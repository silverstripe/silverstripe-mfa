<?php
namespace SilverStripe\MFA\BasicMath;

use SilverStripe\MFA\AuthenticationMethod\AuthenticatorInterface;
use SilverStripe\MFA\AuthenticationMethodInterface;

class Method implements AuthenticationMethodInterface
{
    /**
     * Return the authenticator interface that is used to start and verify login attempts with this method
     *
     * @return AuthenticatorInterface
     */
    public function getAuthenticator()
    {
        return new MethodAuthenticator();
    }

    public function getRegistrar()
    {
        // TODO: Implement getRegistrar() method.
    }
}
