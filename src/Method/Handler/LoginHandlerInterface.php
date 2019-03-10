<?php
namespace SilverStripe\MFA\Method\Handler;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Store\StoreInterface;

/**
 * Represents the base requirements for implementing an MFA Method's LoginHandlerInterface, which has the responsibility
 * of initiating and verifying login attempts for the MFA Method in question.
 */
interface LoginHandlerInterface extends UIComponentProviderInterface
{
    /**
     * Stores any data required to handle a login process with a method, and returns relevant state to be applied to the
     * front-end application managing the process.
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @param RegisteredMethod $method The RegisteredMethod instance that is being verified
     * @return array Props to be passed to a front-end component
     */
    public function start(StoreInterface $store, RegisteredMethod $method);

    /**
     * Verify the request has provided the right information to verify the member that aligns with any sessions state
     * that may have been set prior
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @param RegisteredMethod $registeredMethod The RegisteredMethod instance that is being verified
     * @return bool
     */
    public function verify(HTTPRequest $request, StoreInterface $store, RegisteredMethod $registeredMethod);

    /**
     * Provide a localised string that serves as a lead in for choosing this option for authentication
     *
     * eg. "Enter one of your recovery codes"
     *
     * @return string
     */
    public function getLeadInLabel();
}
