<?php

namespace SilverStripe\MFA\Tests\Stub\Null;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Dev\TestOnly;
use SilverStripe\MFA\Method\Handler\RegisterHandlerInterface;
use SilverStripe\MFA\State\Result;
use SilverStripe\MFA\Store\StoreInterface;

class RegisterHandler implements RegisterHandlerInterface, TestOnly
{
    /**
     * Stores any data required to handle a registration process with a method, and returns relevant state to be applied
     * to the front-end application managing the process.
     *
     * @param StoreInterface $store An object that hold session data (and the Member) that can be mutated
     * @return array Props to be passed to a front-end component
     */
    public function start(StoreInterface $store): array
    {
        return [];
    }

    /**
     * Confirm that the provided details are valid for a registration returning a Result describing the outcome of this
     * validation. Detail to be persisted against the member should be set as context on the result.
     *
     * @param HTTPRequest $request
     * @param StoreInterface $store
     * @return Result A result of this registration with context set as data to be stored against the RegisteredMethod
     */
    public function register(HTTPRequest $request, StoreInterface $store): Result
    {
        return new Result();
    }

    /**
     * Provide a localised description of this MFA Method.
     *
     * eg. "Verification codes are created by an app on your phone"
     *
     * @return string
     */
    public function getDescription(): string
    {
        return '';
    }

    /**
     * Provide a localised URL to a support article about the registration process for this MFA Method.
     *
     * @return string
     */
    public function getSupportLink(): string
    {
        return '';
    }

    /**
     * Provide a localised string to describe the support link {@see getSupportLink} about this MFA Method.
     *
     * @return string
     */
    public function getSupportText(): string
    {
        return '';
    }

    /**
     * Get the key that a React UI component is registered under (with @silverstripe/react-injector on the front-end)
     *
     * @return string
     */
    public function getComponent(): string
    {
        return '';
    }
}
