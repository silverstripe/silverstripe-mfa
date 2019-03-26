<?php

namespace SilverStripe\MFA\Tests\Stub\BasicMath;

use RuntimeException;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\Model\RegisteredMethod;
use SilverStripe\MFA\Store\StoreInterface;

/**
 * Handles registration processing for the Math Method.
 */
class MethodRegisterHandler implements RegisterHandlerInterface, TestOnly
{
    /**
     * Prepare to register this authentication method against a member by initialising state in session and generating
     * details to provide to a frontend React component
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @return array Props to be passed to a front-end React component
     */
    public function start(StoreInterface $store)
    {
        return [];
    }

    /**
     * Confirm that the provided details are valid, and create a new RegisteredMethod against the member.
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @return array
     * @throws RuntimeException When insufficient user input is provided
     */
    public function register(HTTPRequest $request, StoreInterface $store)
    {
        $parameters = json_decode($request->getBody(), true);

        if (!array_key_exists('number', $parameters)) {
            throw new RuntimeException('The required user input was not provided to register this method');
        }

        return ['number' => $parameters['number']];
    }

    /**
     * Provide a string (possibly passed through i18n) that names this registration method.
     *
     * eg. "Authenticator app"
     *
     * @return string
     */
    public function getName()
    {
        return 'Basic math test';
    }

    /**
     * Provide a string (possibly passed through i18n) that describes this method.
     *
     * eg. "Verification codes are created by an app on your phone"
     *
     * @return string
     */
    public function getDescription()
    {
        return 'Asks you to add numbers together';
    }

    /**
     * Provide a URL to a support article about this registration method.
     *
     * @return string
     */
    public function getSupportLink()
    {
        return 'https://google.com';
    }

    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent()
    {
        return 'BasicMathRegister';
    }
}
