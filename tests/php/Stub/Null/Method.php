<?php

namespace SilverStripe\MFA\Tests\Stub\Null;

use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\MethodInterface;

class Method implements MethodInterface, TestOnly
{

    /**
     * Get a URL segment for this method. This will be used in URL paths for performing authentication by this method
     *
     * @return string
     */
    public function getURLSegment(): string
    {
        return 'null';
    }

    /**
     * Return the LoginHandler that is used to start and verify login attempts with this method
     *
     * @return VerifyHandlerInterface
     */
    public function getVerifyHandler(): VerifyHandlerInterface
    {
        return new VerifyHandler();
    }

    /**
     * Return the RegisterHandler that is used to perform registrations with this method
     *
     * @return RegisterHandlerInterface
     */
    public function getRegisterHandler(): RegisterHandlerInterface
    {
        return new RegisterHandler();
    }

    /**
     * Return a URL to an image to be used as a thumbnail in the MFA login/registration grid for all MFA methods
     *
     * @return string
     */
    public function getThumbnail(): string
    {
        // TODO: Implement getThumbnail() method.
    }

    /**
     * Leverage the Requirements API to ensure client requirements are included. This is called just after the base
     * module requirements are specified
     *
     * @return void
     */
    public function applyRequirements(): void
    {
        // TODO: Implement applyRequirements() method.
    }

    /**
     * Returns whether the method is available to be used from a backend perspective.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * If not available to be used, provide a message to display on the frontend to explain why.
     *
     * @return string
     */
    public function getUnavailableMessage(): string
    {
        // TODO: Implement getUnavailableMessage() method.
    }
}
