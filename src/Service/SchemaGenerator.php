<?php

namespace SilverStripe\MFA\Service;

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\MFA\Exception\MemberNotFoundException;
use SilverStripe\MFA\Extension\MemberExtension;
use SilverStripe\MFA\Store\StoreInterface;
use SilverStripe\Security\Member;
use SilverStripe\Security\Security;

/**
 * Generates a multi-factor authentication frontend app schema from the given request
 */
class SchemaGenerator
{
    use Extensible;
    use Injectable;

    /**
     * @var HTTPRequest
     */
    protected $request;

    /**
     * @var StoreInterface
     */
    protected $store;

    /**
     * @param HTTPRequest $request
     * @param StoreInterface $store
     */
    public function __construct(HTTPRequest $request, StoreInterface $store)
    {
        $this->setRequest($request);
        $this->setStore($store);
    }

    /**
     * Gets the schema data for the multi factor authentication app, using the current Member as context
     *
     * @return array
     */
    public function getSchema()
    {
        $registeredMethods = $this->getRegisteredMethods();

        // Skip registration details if the user has already registered this method
        $exclude = array_column($registeredMethods, 'urlSegment');

        $schema = [
            'registeredMethods' => $registeredMethods,
            'availableMethods' => $this->getAvailableMethods($exclude),
            'defaultMethod' => $this->getDefaultMethod(),
        ];

        $this->extend('updateSchema', $schema);

        return $schema;
    }

    /**
     * @return Member|MemberExtension
     * @throws MemberNotFoundException
     */
    public function getMember()
    {
        $member = $this->store->getMember() ?: Security::getCurrentUser();

        // If we don't have a valid member we shouldn't be here...
        if (!$member) {
            throw new MemberNotFoundException();
        }

        return $member;
    }

    /**
     * Get a list of methods registered to the user
     *
     * @return array[]
     */
    protected function getRegisteredMethods()
    {
        $registeredMethods = $this->getMember()->RegisteredMFAMethods();

        // Generate a map of URL Segments to 'lead in labels', which are used to describe the method in the login UI
        $registeredMethodDetails = [];
        foreach ($registeredMethods as $registeredMethod) {
            $method = $registeredMethod->getMethod();

            $registeredMethodDetails[] = [
                'urlSegment' => $method->getURLSegment(),
                'leadInLabel' => $method->getLoginHandler()->getLeadInLabel()
            ];
        }

        return $registeredMethodDetails;
    }

    /**
     * Get details in a list for all available methods, optionally excluding those with urlSegments provided in
     * $exclude
     *
     * @param array $exclude
     * @return array[]
     */
    protected function getAvailableMethods(array $exclude = [])
    {
        // Prepare an array to hold details for methods available to register
        $availableMethods = [];

        // Get all methods enabled on the site
        $allMethods = MethodRegistry::singleton()->getMethods();

        // Compile details for methods that aren't already registered to the user
        foreach ($allMethods as $method) {
            if (in_array($method->getURLSegment(), $exclude)) {
                continue;
            }

            $registerHandler = $method->getRegisterHandler();

            $availableMethods[] = [
                'urlSegment' => $method->getURLSegment(),
                'name' => $registerHandler->getName(),
                'description' => $registerHandler->getDescription(),
                'supportLink' => $registerHandler->getSupportLink(),
            ];
        }

        return $availableMethods;
    }

    /**
     * Get the URL Segment for the configured default method on the current member, or null if none is configured
     *
     * @return string|null
     */
    protected function getDefaultMethod()
    {
        $defaultMethod = $this->getMember()->DefaultRegisteredMethod;
        return $defaultMethod ? $defaultMethod->getMethod()->getURLSegment() : null;
    }

    /**
     * @param HTTPRequest $request
     * @return $this
     */
    public function setRequest(HTTPRequest $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * @param StoreInterface $store
     * @return $this
     */
    public function setStore(StoreInterface $store)
    {
        $this->store = $store;
        return $this;
    }
}
