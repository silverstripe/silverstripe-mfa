<?php

namespace SilverStripe\MFA\Method\Handler;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Store\StoreInterface;

/**
 * Represents the base requirements for implementing an MFA Method's RegisterHandler, which has the responsibility
 * of initiating and managing registration of the MFA Method in question against the current Member.
 */
interface RegisterHandlerInterface extends HandlerInterface
{
    /**
     * Confirm that the provided details are valid, and create a new RegisteredMethod against the member.
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @return bool
     */
    public function register(HTTPRequest $request, StoreInterface $store);

    /**
     * Provide a localised name for this MFA Method.
     *
     * eg. "Authenticator app"
     *
     * @return string
     */
    public function getName();

    /**
     * Provide a localised description of this MFA Method.
     *
     * eg. "Verification codes are created by an app on your phone"
     *
     * @return string
     */
    public function getDescription();

    /**
     * Provide a localised URL to a support article about the registration process for this MFA Method.
     *
     * @return string
     */
    public function getSupportLink();
}
