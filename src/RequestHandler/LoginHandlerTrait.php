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
use SilverStripe\MFA\Store\StoreInterface;

/**
 * This trait encapsulates logic that can be added to a `RequestHandler` to work with logging in using MFA front-end
 * app. It provides two main methods; @see createStartLoginResponse - a response that can be easily consumed by the MFA
 * app to prompt a login, and @see verifyLoginRequest - used to verify a request sent by the MFA app containing the
 * login attempt.
 */
trait LoginHandlerTrait
{
    /**
     * @return MethodRegistry
     */
    protected function getMethodRegistry(): MethodRegistry
    {
        return MethodRegistry::singleton();
    }

    /**
     * @return RegisteredMethodManager
     */
    protected function getRegisteredMethodManager(): RegisteredMethodManager
    {
        return RegisteredMethodManager::singleton();
    }

    /**
     * Create an HTTPResponse that provides information to the client side React MFA app to prompt the user to login
     * with their configured MFA method
     *
     * @param StoreInterface $store
     * @param MethodInterface|null $requestedMethod
     * @return HTTPResponse
     */
    protected function createStartLoginResponse(
        StoreInterface $store,
        ?MethodInterface $requestedMethod = null
    ): HTTPResponse {
        $registeredMethod = null;
        $member = $store->getMember();

        // Use a requested method if provided
        if ($requestedMethod) {
            $registeredMethod = $this->getRegisteredMethodManager()->getFromMember($member, $requestedMethod);
        }

        // ...Or use the default (TODO: Should we have the default as a fallback? Maybe just if no method is specified?)
        if (!$registeredMethod) {
            $registeredMethod = $member->DefaultRegisteredMethod;
        }

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

            return $this->jsonResponse(
                ['errors' => [$message]],
                400
            );
        }

        // Mark the given method as started within the store
        $store->setMethod($registeredMethod->getMethod()->getURLSegment());
        // Allow the authenticator to begin the process and generate some data to pass through to the front end
        $data = $registeredMethod->getLoginHandler()->start($store, $registeredMethod);

        // Respond with our method
        return $this->jsonResponse($data ?: []);
    }

    /**
     * Attempt to verify a login attempt provided by the given request
     *
     * @param StoreInterface $store
     * @param HTTPRequest $request
     * @return bool
     */
    protected function verifyLoginRequest(StoreInterface $store, HTTPRequest $request): bool
    {
        $method = $store->getMethod();
        $methodInstance = $method ? $this->getMethodRegistry()->getMethodByURLSegment($method) : null;

        // The method must be tracked in session. If it's missing we can't continue
        if (!$methodInstance) {
            throw new InvalidMethodException('There is no method tracked in a store for this request');
        }

        // Get the member and authenticator ready
        $member = $store->getMember();
        $registeredMethod = $this->getRegisteredMethodManager()->getFromMember($member, $methodInstance);
        $authenticator = $registeredMethod->getLoginHandler();

        if ($authenticator->verify($request, $store, $registeredMethod)) {
            $store->addVerifiedMethod($method);
            $store->save($request);
            $this->extend('onMethodVerificationSuccess', $member, $methodInstance);
            return true;
        }

        $this->extend('onMethodVerificationFailure', $member, $methodInstance);
        return false;
    }

    /**
     * Indicates the current member has verified with MFA methods enough to be considered "verified"
     *
     * @param StoreInterface $store
     * @return bool
     */
    protected function isLoginComplete(StoreInterface $store): bool
    {
        // Pull the successful methods from session
        $successfulMethods = $store->getVerifiedMethods();

        // Zero is "not complete". There's different config for optional MFA
        if (!is_array($successfulMethods) || !count($successfulMethods)) {
            return false;
        }

        return count($successfulMethods) >= Config::inst()->get(EnforcementManager::class, 'required_mfa_methods');
    }

    /**
     * Respond with the given array as a JSON response
     *
     * @param array $response
     * @param int $code The HTTP response code to set on the response
     * @return HTTPResponse
     */
    protected function jsonResponse(array $response, int $code = 200): HTTPResponse
    {
        return HTTPResponse::create(json_encode($response))
            ->addHeader('Content-Type', 'application/json')
            ->setStatusCode($code);
    }
}
