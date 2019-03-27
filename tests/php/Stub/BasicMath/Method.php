<?php
namespace SilverStripe\MFA\Tests\Stub\BasicMath;

use SilverStripe\Core\Manifest\ModuleLoader;
use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\State\AvailableMethodDetails;

class Method implements MethodInterface, TestOnly
{
    /**
     * Get a URL segment for this method. This will be used in URL paths for performing authentication by this method
     *
     * @return string
     */
    public function getURLSegment()
    {
        return 'basic-math';
    }

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

    public function getThumbnail()
    {
        return ModuleLoader::getModule('silverstripe/mfa')->getResource('client/dist/images/totp.svg')->getURL();
    }

    public function getDetails()
    {
        return new AvailableMethodDetails($this);
    }
}
