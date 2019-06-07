<?php

namespace SilverStripe\MFA\RequestHandler;

use Exception;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\MFA\Method\MethodInterface;
use SilverStripe\MFA\Service\RegisteredMethodManager;
use SilverStripe\MFA\State\RegisteredMethodDetailsInterface;
use SilverStripe\MFA\State\Result;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\ORM\ValidationResult;
use SilverStripe\Security\SecurityToken;

/**
 * This trait encapsulates logic that can be added to a `RequestHandler` to work with registering MFA authenticators
 * using the MFA front-end app. It provides two main methods; @see createStartRegistrationResponse - creates a response
 * that can be easily consumed by the MFA app to start the registration process for a method, and
 * @see completeRegistrationRequest - used to complete the registration flow for a method using details sent back by the
 * MFA app.
 */
trait RegistrationHandlerTrait
{
    /**
     * Create a response that can be consumed by a front-end for starting a registration
     *
     * @param StoreInterface $store
     * @param MethodInterface $method
     * @param bool $allowReregistration By default this method will return an error response when registering methods
     *                                  that already have a registration.
     * @return HTTPResponse
     */
    public function createStartRegistrationResponse(
        StoreInterface $store,
        MethodInterface $method,
        bool $allowReregistration = false
    ): HTTPResponse {
        $member = $store->getMember();

        // Sanity check that the method hasn't already been registered
        $existingRegisteredMethod = RegisteredMethodManager::singleton()->getFromMember($member, $method);

        $response = HTTPResponse::create()
            ->addHeader('Content-Type', 'application/json');

        if (!$allowReregistration && $existingRegisteredMethod) {
            return $response->setBody(json_encode(['errors' => [_t(
                __CLASS__ . '.METHOD_ALREADY_REGISTERED',
                'That method has already been registered against this Member'
            )]]))->setStatusCode(400);
        }

        // Mark the given method as started within the session
        $store->setMethod($method->getURLSegment());
        // Allow the registration handler to begin the process and generate some data to pass through to the front-end
        $data = $method->getRegisterHandler()->start($store);

        // Add a CSRF token
        $token = SecurityToken::inst();
        $token->reset();
        $data[$token->getName()] = $token->getValue();

        return $response->setBody(json_encode($data));
    }

    /**
     * Complete a registration request, returning a result object with a message and context for the result of the
     * registration attempt.
     *
     * @param StoreInterface $store
     * @param MethodInterface $method
     * @param HTTPRequest $request
     * @return Result
     * @throws \SilverStripe\ORM\ValidationException
     */
    public function completeRegistrationRequest(
        StoreInterface $store,
        MethodInterface $method,
        HTTPRequest $request
    ): Result {
        if (!SecurityToken::inst()->checkRequest($request)) {
            return Result::create(false, _t(
                __CLASS__ . '.CSRF_FAILURE',
                'Your request timed out. Please refresh and try again'
            ), ['code' => 403]);
        }

        $storedMethodName = $store->getMethod();

        // If a registration process hasn't been initiated in a previous request, calling this method is invalid
        if (!$storedMethodName) {
            return Result::create(false, _t(__CLASS__ . '.NO_REGISTRATION_IN_PROGRESS', 'No registration in progress'));
        }

        // Assert the method in progress matches the request for completion
        if ($storedMethodName !== $method->getURLSegment()) {
            return Result::create(
                false,
                _t(__CLASS__ . '.METHOD_MISMATCH', 'Method does not match registration in progress')
            );
        }

        $registrationHandler = $method->getRegisterHandler();
        $result = $registrationHandler->register($request, $store);

        $member = $store->getMember();
        if ($result->isSuccessful()) {
            RegisteredMethodManager::singleton()
                ->registerForMember($member, $method, $result->getContext());
        } else {
            $this->extend('onRegisterMethodFailure', $member, $method);
        }

        // Replace the context with detail of the registered method
        return $result->setContext([
            'registeredMethod' => Injector::inst()->create(RegisteredMethodDetailsInterface::class, $method)
        ]);
    }
}
