<?php
namespace SilverStripe\MFA\Tests\Stub\BasicMath;

use Director;
use SilverStripe\Core\Manifest\ModuleLoader; // Not present in SS3
use TestOnly;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\State\AvailableMethodDetails;
use SilverStripe\MFA\State\AvailableMethodDetailsInterface;

class Method implements MethodInterface, TestOnly
{
    /**
     * Get a URL segment for this method. This will be used in URL paths for performing authentication by this method
     *
     * @return string
     */
    public function getURLSegment(): string
    {
        return 'basic-math';
    }

    /**
     * Return the VerifyHandler that is used to start and check verification attempts with this method
     *
     * @return VerifyHandlerInterface
     */
    public function getVerifyHandler(): VerifyHandlerInterface
    {
        return new MethodVerifyHandler();
    }

    /**
     * Return the RegisterHandler that is used to perform registrations with this method
     *
     * @return RegisterHandlerInterface
     */
    public function getRegisterHandler(): RegisterHandlerInterface
    {
        return new MethodRegisterHandler();
    }

    public function getThumbnail(): string
    {
        return (string) ModuleLoader::getModule('silverstripe/mfa')
            ->getResource('client/dist/images/totp.svg')
            ->getURL();
    }

    public function applyRequirements(): void
    {
        // noop
    }

    public function isAvailable(): bool
    {
        return Director::isDev();
    }

    public function getUnavailableMessage(): string
    {
        return 'This is a test authenticator, only available in dev mode for tests.';
    }
}
