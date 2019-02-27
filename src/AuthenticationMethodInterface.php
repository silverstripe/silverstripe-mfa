<?php
namespace SilverStripe\MFA;

use SilverStripe\MFA\AuthenticationMethod\AuthenticatorInterface;

interface AuthenticationMethodInterface
{
    /**
     * Return the authenticator interface that is used to start and verify login attempts with this method
     *
     * @return AuthenticatorInterface
     */
    public function getAuthenticator();

    public function getRegistrar();
}
