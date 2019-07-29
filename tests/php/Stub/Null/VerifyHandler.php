<?php

namespace SilverStripe\MFA\Tests\Stub\Null;

use SS_HTTPRequest as HTTPRequest;
use TestOnly;
use SilverStripe\MFA\Method\Handler\VerifyHandlerInterface;
use MFARegisteredMethod as RegisteredMethod;
use SilverStripe\MFA\State\Result;
use SilverStripe\MFA\Store\StoreInterface;

class VerifyHandler implements VerifyHandlerInterface, TestOnly
{
    /**
     * Stores any data required to handle a login process with a method, and returns relevant state to be applied to the
     * front-end application managing the process.
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @param RegisteredMethod $method The RegisteredMethod instance that is being verified
     * @return array Props to be passed to a front-end component
     */
    public function start(StoreInterface $store, RegisteredMethod $method): array
    {
        // TODO: Implement start() method.
    }

    /**
     * Verify the request has provided the right information to verify the member that aligns with any sessions state
     * that may have been set prior
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @param RegisteredMethod $registeredMethod The RegisteredMethod instance that is being verified
     * @return Result
     */
    public function verify(HTTPRequest $request, StoreInterface $store, RegisteredMethod $registeredMethod): Result
    {
        // TODO: Implement verify() method.
    }

    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent(): string
    {
        // TODO: Implement getComponent() method.
    }
}
