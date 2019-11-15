<?php

declare(strict_types=1);

namespace SilverStripe\MFA\Method;

use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;

/**
 * Defines an Authentication Method, which serves as an additional factor for authentication beyond the standard
 * username / password method.
 */
interface MethodInterface
{
    /**
     * Provide a localised name for this MFA Method.
     *
     * eg. "Authenticator app"
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Get a URL segment for this method. This will be used in URL paths for performing authentication by this method
     *
     * @return string
     */
    public function getURLSegment(): string;

    /**
     * Return the VerifyHandler that is used to start and check verification attempts with this method
     *
     * @return VerifyHandlerInterface
     */
    public function getVerifyHandler(): VerifyHandlerInterface;

    /**
     * Return the RegisterHandler that is used to perform registrations with this method
     *
     * @return RegisterHandlerInterface
     */
    public function getRegisterHandler(): RegisterHandlerInterface;

    /**
     * Return a URL to an image to be used as a thumbnail in the MFA login/registration grid for all MFA methods
     *
     * @return string
     */
    public function getThumbnail(): string;

    /**
     * Leverage the Requirements API to ensure client requirements are included. This is called just after the base
     * module requirements are specified
     *
     * @return void
     */
    public function applyRequirements(): void;

    /**
     * Returns whether the method is available to be used from a backend perspective.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * If not available to be used, provide a message to display on the frontend to explain why.
     *
     * @return string
     */
    public function getUnavailableMessage(): string;
}
