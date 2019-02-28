<?php
namespace SilverStripe\MFA\Method\Handler;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Store\StoreInterface;

/**
 * Represents the base requirements for implementing an MFA Method's LoginHandlerInterface, which has the responsibility
 * of initiating and verifying login attempts for the MFA Method in question.
 */
interface LoginHandlerInterface extends HandlerInterface
{
    /**
     * Verify the request has provided the right information to verify the member that aligns with any sessions state
     * that may have been set prior
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @return bool
     */
    public function verify(HTTPRequest $request, StoreInterface $store);

    /**
     * Provide a localised string that serves as a lead in for choosing this option for authentication
     *
     * eg. "Enter one of your recovery codes"
     *
     * @return string
     */
    public function getLeadInLabel();
}
