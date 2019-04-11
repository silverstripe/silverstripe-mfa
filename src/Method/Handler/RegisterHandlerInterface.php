<?php declare(strict_types=1);

namespace SilverStripe\MFA\Method\Handler;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\MFA\Store\StoreInterface;

/**
 * Represents the base requirements for implementing an MFA Method's RegisterHandler, which has the responsibility
 * of initiating and managing registration of the MFA Method in question against the current Member.
 */
interface RegisterHandlerInterface
{
    /**
     * Stores any data required to handle a registration process with a method, and returns relevant state to be applied
     * to the front-end application managing the process.
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @return array Props to be passed to a front-end component
     */
    public function start(StoreInterface $store): array;

    /**
     * Confirm that the provided details are valid, and return an array of "data" to store on the RegisteredMethod
     * created for this registration.
     *
     * An Exception should be thrown if the registration could not be completed
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @return array Data to be stored against the created RegisteredMethod
     */
    public function register(HTTPRequest $request, StoreInterface $store): array;

    /**
     * Provide a localised name for this MFA Method.
     *
     * eg. "Authenticator app"
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Provide a localised description of this MFA Method.
     *
     * eg. "Verification codes are created by an app on your phone"
     *
     * @return string
     */
    public function getDescription(): string;

    /**
     * Provide a localised URL to a support article about the registration process for this MFA Method.
     *
     * @return string
     */
    public function getSupportLink(): string;

    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent(): string;
}
