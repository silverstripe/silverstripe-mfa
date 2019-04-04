<?php
namespace SilverStripe\MFA\Method;

use SilverStripe\MFA\Method\Handler\LoginHandlerInterface;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\State\AvailableMethodDetailsInterface;

/**
 * Defines an Authentication Method, which serves as an additional factor for authentication beyond the standard
 * username / password method.
 */
interface MethodInterface
{
    /**
     * Get a URL segment for this method. This will be used in URL paths for performing authentication by this method
     *
     * @return string
     */
    public function getURLSegment();

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

    /**
     * Return a URL to an image to be used as a thumbnail in the MFA login/registration grid for all MFA methods
     *
     * @return string
     */
    public function getThumbnail();

    /**
     * Leverage the Requirements API to ensure client requirements are included. This is called just after the base
     * module requirements are specified
     *
     * @return void
     */
    public function applyRequirements();

    /**
     * @return AvailableMethodDetailsInterface
     */
    public function getDetails();
}
