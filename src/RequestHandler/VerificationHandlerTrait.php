<?php

namespace SilverStripe\MFA\RequestHandler;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Config\Config;
use SilverStripe\MFA\Exception\InvalidMethodException;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Service\EnforcementManager;
use SilverStripe\MFA\Service\MethodRegistry;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\State\Result;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\Security\SecurityToken;

/**
 * This trait encapsulates logic that can be added to a `RequestHandler` to work with logging in using MFA front-end
 * app. It provides two main methods; @see createStartVerificationResponse - a response that can be easily consumed by
 * the MFA app to prompt a login, and @see completeVerificationRequest - used to verify a request sent by the MFA app
 * containing the login attempt.
 */
trait VerificationHandlerTrait
{
    /**
     * Create an HTTPResponse that provides information to the client side React MFA app to prompt the user to login
     * with their configured MFA method
     *
     * @param StoreInterface $store
     * @param MethodInterface|null $requestedMethod
     * @return HTTPResponse
     */
    protected function createStartVerificationResponse(
        StoreInterface $store,
        ?MethodInterface $requestedMethod = null
    ): HTTPResponse {
        $registeredMethod = null;
        $member = $store->getMember();

        // Use a requested method if provided
        if ($requestedMethod) {
            $registeredMethod = RegisteredMethodManager::singleton()->getFromMember($member, $requestedMethod);
        }

        // Use the default method if there's no requested method
        if (!$registeredMethod) {
            $registeredMethod = $member->DefaultRegisteredMethod;
        }

        $response = HTTPResponse::create()
            ->addHeader('Content-Type', 'application/json');

        // We can't proceed with login if the Member doesn't have this method registered
        if (!$registeredMethod) {
            // We can display a specific message if there was no method specified
            if (!$requestedMethod) {
                $message = _t(
                    __CLASS__ . '.METHOD_NOT_PROVIDED',
                    'No method was provided to login with and the Member has no default'
                );
            } else {
                $message = _t(__CLASS__ . '.METHOD_NOT_REGISTERED', 'Member does not have this method registered');
            }

            return $response->setBody(json_encode(['errors' => [$message]]))->setStatusCode(400);
        }

        // Mark the given method as started within the store
        $store->setMethod($registeredMethod->getMethod()->getURLSegment());
        // Allow the authenticator to begin the process and generate some data to pass through to the front end
        $data = $registeredMethod->getVerifyHandler()->start($store, $registeredMethod) ?: [];

        // Add a CSRF token
        $token = SecurityToken::inst();
        $token->reset();
        $data[$token->getName()] = $token->getValue();

        // Respond with our method
        return $response->setBody(json_encode($data));
    }

    /**
     * Attempt to verify a login attempt provided by the given request
     *
     * @param StoreInterface $store
     * @param HTTPRequest $request
     * @return Result
     * @throws InvalidMethodException
     */
    protected function completeVerificationRequest(StoreInterface $store, HTTPRequest $request): Result
    {
        if (!SecurityToken::inst()->checkRequest($request)) {
            return Result::create(false, _t(
                __CLASS__ . '.CSRF_FAILURE',
                'Your request timed out. Please refresh and try again'
            ), ['code' => 403]);
        }

        $method = $store->getMethod();
        $methodInstance = $method ? MethodRegistry::singleton()->getMethodByURLSegment($method) : null;

        // The method must be tracked in session. If it's missing we can't continue
        if (!$methodInstance) {
            throw new InvalidMethodException('There is no method tracked in a store for this request');
        }

        // Get the member and authenticator ready
        $member = $store->getMember();
        $registeredMethod = RegisteredMethodManager::singleton()->getFromMember($member, $methodInstance);
        $authenticator = $registeredMethod->getVerifyHandler();

        $result = $authenticator->verify($request, $store, $registeredMethod);
        if ($result->isSuccessful()) {
            $store->addVerifiedMethod($method);
            $store->save($request);
            $this->extend('onMethodVerificationSuccess', $member, $methodInstance);
            return $result;
        }

        $this->extend('onMethodVerificationFailure', $member, $methodInstance);
        return $result;
    }

    /**
     * Indicates the current member has verified with MFA methods enough to be considered "verified"
     *
     * @param StoreInterface $store
     * @return bool
     */
    protected function isVerificationComplete(StoreInterface $store): bool
    {
        // Pull the successful methods from session
        $successfulMethods = $store->getVerifiedMethods();

        // Zero is "not complete". There's different config for optional MFA
        if (!is_array($successfulMethods) || !count($successfulMethods ?? [])) {
            return false;
        }

        $required = Config::inst()->get(EnforcementManager::class, 'required_mfa_methods');
        return count($successfulMethods ?? []) >= $required;
    }
}
